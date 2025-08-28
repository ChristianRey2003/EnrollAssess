/*===========================================
  EXAM INTERFACE JAVASCRIPT
  Optimized JS for exam-taking interface
===========================================*/

// Exam management functionality
window.ExamInterface = {
    // Timer functionality
    timer: {
        duration: 0, // Will be set from server
        remaining: 0,
        interval: null,
        warningThreshold: 300, // 5 minutes

        start(durationInSeconds) {
            this.duration = durationInSeconds;
            this.remaining = durationInSeconds;
            this.updateDisplay();
            
            this.interval = setInterval(() => {
                this.remaining--;
                this.updateDisplay();
                
                // Check for warning threshold
                if (this.remaining <= this.warningThreshold) {
                    this.showWarning();
                }
                
                // Auto-submit when time is up
                if (this.remaining <= 0) {
                    this.stop();
                    ExamInterface.autoSubmit();
                }
            }, 1000);
        },

        stop() {
            if (this.interval) {
                clearInterval(this.interval);
                this.interval = null;
            }
        },

        updateDisplay() {
            const hours = Math.floor(this.remaining / 3600);
            const minutes = Math.floor((this.remaining % 3600) / 60);
            const seconds = this.remaining % 60;
            
            const display = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            const timerElement = document.getElementById('exam-timer');
            if (timerElement) {
                timerElement.textContent = display;
            }
        },

        showWarning() {
            const timerElement = document.getElementById('exam-timer');
            if (timerElement) {
                timerElement.classList.add('warning');
            }
            
            // Show warning message
            this.showTimeWarningModal();
        },

        showTimeWarningModal() {
            if (!document.getElementById('time-warning-shown')) {
                const warning = document.createElement('div');
                warning.id = 'time-warning-shown';
                warning.innerHTML = `
                    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                        <div class="bg-white p-6 rounded-lg max-w-md mx-4">
                            <h3 class="text-lg font-bold text-red-600 mb-2">⚠️ Time Warning</h3>
                            <p class="mb-4">You have less than 5 minutes remaining. Please review your answers.</p>
                            <button onclick="this.closest('#time-warning-shown').remove()" 
                                    class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                                Understood
                            </button>
                        </div>
                    </div>
                `;
                document.body.appendChild(warning);
                
                // Auto-remove after 10 seconds
                setTimeout(() => {
                    if (warning.parentNode) {
                        warning.remove();
                    }
                }, 10000);
            }
        }
    },

    // Question navigation
    navigation: {
        currentQuestion: 1,
        totalQuestions: 0,
        answers: {},

        init(totalQuestions) {
            this.totalQuestions = totalQuestions;
            this.updateProgress();
        },

        goToQuestion(questionNumber) {
            if (questionNumber >= 1 && questionNumber <= this.totalQuestions) {
                this.currentQuestion = questionNumber;
                this.updateProgress();
                this.scrollToTop();
            }
        },

        nextQuestion() {
            if (this.currentQuestion < this.totalQuestions) {
                this.goToQuestion(this.currentQuestion + 1);
            }
        },

        previousQuestion() {
            if (this.currentQuestion > 1) {
                this.goToQuestion(this.currentQuestion - 1);
            }
        },

        updateProgress() {
            const progressBar = document.getElementById('progress-fill');
            if (progressBar) {
                const progress = (this.currentQuestion / this.totalQuestions) * 100;
                progressBar.style.width = `${progress}%`;
            }

            // Update question counter
            const counter = document.getElementById('question-counter');
            if (counter) {
                counter.textContent = `Question ${this.currentQuestion} of ${this.totalQuestions}`;
            }

            // Update navigation buttons
            this.updateNavigationButtons();
        },

        updateNavigationButtons() {
            const prevBtn = document.getElementById('prev-question');
            const nextBtn = document.getElementById('next-question');
            
            if (prevBtn) {
                prevBtn.disabled = this.currentQuestion === 1;
            }
            
            if (nextBtn) {
                nextBtn.disabled = this.currentQuestion === this.totalQuestions;
            }
        },

        scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    },

    // Answer management
    answers: {
        data: {},

        saveAnswer(questionId, answer) {
            this.data[questionId] = answer;
            this.updateLocalStorage();
            this.markQuestionAsAnswered(questionId);
        },

        getAnswer(questionId) {
            return this.data[questionId] || null;
        },

        updateLocalStorage() {
            // Save answers to localStorage as backup
            try {
                localStorage.setItem('exam_answers_backup', JSON.stringify(this.data));
                localStorage.setItem('exam_answers_timestamp', Date.now().toString());
            } catch (e) {
                console.warn('Could not save answers to localStorage:', e);
            }
        },

        loadFromLocalStorage() {
            try {
                const saved = localStorage.getItem('exam_answers_backup');
                const timestamp = localStorage.getItem('exam_answers_timestamp');
                
                // Only load if saved within last 4 hours
                if (saved && timestamp && (Date.now() - parseInt(timestamp)) < 4 * 60 * 60 * 1000) {
                    this.data = JSON.parse(saved);
                    return true;
                }
            } catch (e) {
                console.warn('Could not load answers from localStorage:', e);
            }
            return false;
        },

        clearLocalStorage() {
            try {
                localStorage.removeItem('exam_answers_backup');
                localStorage.removeItem('exam_answers_timestamp');
            } catch (e) {
                console.warn('Could not clear localStorage:', e);
            }
        },

        markQuestionAsAnswered(questionId) {
            const questionElement = document.querySelector(`[data-question-id="${questionId}"]`);
            if (questionElement) {
                questionElement.classList.add('answered');
            }
        },

        getAnsweredCount() {
            return Object.keys(this.data).length;
        }
    },

    // Auto-save functionality
    autoSave: {
        interval: null,
        isEnabled: true,

        start() {
            if (!this.isEnabled) return;
            
            this.interval = setInterval(() => {
                this.saveToServer();
            }, 30000); // Save every 30 seconds
        },

        stop() {
            if (this.interval) {
                clearInterval(this.interval);
                this.interval = null;
            }
        },

        async saveToServer() {
            try {
                const response = await fetch('/exam/auto-save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        answers: ExamInterface.answers.data,
                        current_question: ExamInterface.navigation.currentQuestion,
                        timestamp: Date.now()
                    })
                });

                if (response.ok) {
                    this.showSaveIndicator();
                }
            } catch (error) {
                console.warn('Auto-save failed:', error);
            }
        },

        showSaveIndicator() {
            const indicator = document.getElementById('save-indicator');
            if (indicator) {
                indicator.style.opacity = '1';
                setTimeout(() => {
                    indicator.style.opacity = '0';
                }, 2000);
            }
        }
    },

    // Submit functionality
    async submit() {
        const answeredCount = this.answers.getAnsweredCount();
        const totalQuestions = this.navigation.totalQuestions;
        
        if (answeredCount < totalQuestions) {
            const confirmed = confirm(
                `You have answered ${answeredCount} out of ${totalQuestions} questions. ` +
                'Are you sure you want to submit your exam?'
            );
            
            if (!confirmed) {
                return;
            }
        }

        // Stop timer and auto-save
        this.timer.stop();
        this.autoSave.stop();

        // Submit to server
        try {
            const form = document.getElementById('exam-form');
            if (form) {
                // Add final answers to form
                for (const [questionId, answer] of Object.entries(this.answers.data)) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `answers[${questionId}]`;
                    input.value = answer;
                    form.appendChild(input);
                }

                form.submit();
            }
        } catch (error) {
            console.error('Submit failed:', error);
            alert('There was an error submitting your exam. Please try again.');
        }

        // Clear localStorage after successful submission
        this.answers.clearLocalStorage();
    },

    autoSubmit() {
        alert('Time is up! Your exam will be submitted automatically.');
        this.submit();
    },

    // Initialize the exam interface
    init(config) {
        // Load saved answers if available
        this.answers.loadFromLocalStorage();

        // Initialize navigation
        this.navigation.init(config.totalQuestions);

        // Start timer if provided
        if (config.timeRemaining) {
            this.timer.start(config.timeRemaining);
        }

        // Start auto-save
        this.autoSave.start();

        // Setup event listeners
        this.setupEventListeners();

        // Setup visibility change handler (tab switching detection)
        this.setupVisibilityHandler();
    },

    setupEventListeners() {
        // Answer selection
        document.addEventListener('change', (e) => {
            if (e.target.type === 'radio' && e.target.name.startsWith('question_')) {
                const questionId = e.target.name.replace('question_', '');
                this.answers.saveAnswer(questionId, e.target.value);
            }
        });

        // Navigation buttons
        document.addEventListener('click', (e) => {
            if (e.target.id === 'next-question') {
                this.navigation.nextQuestion();
            } else if (e.target.id === 'prev-question') {
                this.navigation.previousQuestion();
            } else if (e.target.id === 'submit-exam') {
                this.submit();
            }
        });

        // Prevent accidental page leave
        window.addEventListener('beforeunload', (e) => {
            e.preventDefault();
            e.returnValue = 'Are you sure you want to leave? Your exam progress may be lost.';
            return e.returnValue;
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                switch (e.key) {
                    case 'ArrowLeft':
                        e.preventDefault();
                        this.navigation.previousQuestion();
                        break;
                    case 'ArrowRight':
                        e.preventDefault();
                        this.navigation.nextQuestion();
                        break;
                }
            }
        });
    },

    setupVisibilityHandler() {
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                console.log('Tab switched - exam monitoring active');
                // Could implement tab-switching detection here if needed
            }
        });
    }
};

// Initialize exam interface when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        // Configuration will be passed from the server
        const config = window.examConfig || {
            totalQuestions: 20,
            timeRemaining: 3600 // 1 hour default
        };
        ExamInterface.init(config);
    });
} else {
    const config = window.examConfig || {
        totalQuestions: 20,
        timeRemaining: 3600
    };
    ExamInterface.init(config);
}

// Export for module usage
export default ExamInterface;
