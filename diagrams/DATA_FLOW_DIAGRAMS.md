# EnrollAssess System: Data Flow Diagrams

## Overview

The EnrollAssess system implements a comprehensive enrollment and assessment management platform designed to streamline the university admission process. The system utilizes a modular architecture that processes applicant data through multiple stages: authentication, examination, interview evaluation, and final decision-making. This document presents the data flow diagrams that illustrate how information moves through the system and describes each component's role in the overall architecture.

## Diagram Notation

The data flow diagrams follow standard DFD conventions:

- **Circles (Blue)**: Represent processes or transformations that manipulate data
- **Rectangles (Dark Gray)**: Represent external entities (users or external systems) that interact with the system
- **Open-ended Rectangles (Red borders)**: Represent data stores where information persists
- **Arrows (Red)**: Represent data flows showing the direction of information movement

---

## Level 0 Data Flow Diagram (Context Diagram)

### Purpose

The Level 0 DFD provides a high-level view of the EnrollAssess system as a single process, showing its interactions with external entities. This diagram establishes the system boundary and identifies all external actors and data exchanges.

### Components

#### Central System

**EnrollAssess (Process 0)**

The central system represents the entire enrollment and assessment management platform. It receives inputs from various external entities, processes the information according to business rules, and produces outputs that support the admission decision-making process. The system maintains data integrity, enforces security policies, and coordinates all subsystem operations.

#### External Entities

**1. Applicants**

Applicants represent prospective students who seek admission to the university. They interact with the system through a web interface to complete entrance examinations and submit required information. The system provides applicants with access codes that serve as secure, one-time authentication credentials. After completing examinations, applicants receive immediate feedback including scores and performance analytics.

**Data Flows:**
- **Incoming**: Access Code (authentication credentials provided to the system)
- **Outgoing**: Exam & Results (examination interface and score reports delivered to applicants)

**2. Admin (Department Head)**

The admin entity represents department heads who possess full administrative privileges. Admins manage the entire system lifecycle, including user creation, examination configuration, interview scheduling, and admission decisions. They utilize comprehensive dashboards to monitor system performance and make data-driven decisions.

**Data Flows:**
- **Incoming**: Manage System (administrative commands, configuration changes, and applicant assignments)
- **Outgoing**: Reports & Analytics (statistical summaries, performance metrics, and operational insights)

**3. Instructors**

Instructors represent faculty members assigned to conduct applicant interviews. They evaluate candidates using standardized rubrics and submit detailed assessments through the system. Instructors access assigned applicant portfolios, schedule appointments, and provide qualitative feedback that complements quantitative examination scores.

**Data Flows:**
- **Incoming**: Interview Scores (evaluation results and qualitative assessments submitted by instructors)
- **Outgoing**: Assignments (interview schedules and applicant information delivered to instructors)

**4. Email System**

The email system represents external mail services (AWS SES or SMTP) that deliver notifications to system users. The system queues email jobs asynchronously to prevent blocking main application processes. Email templates provide consistent formatting for access codes, interview schedules, and status updates.

**Data Flows:**
- **Incoming**: Send Notifications (formatted email messages with recipient information)
- **Outgoing**: Email Requests (requests from the system to dispatch notifications)

**5. Database**

The database represents the MySQL data persistence layer that stores all system information. It maintains referential integrity through foreign key constraints and supports transactional operations to ensure data consistency. The database stores user accounts, examination questions, applicant profiles, test results, interview evaluations, and system configuration.

**Data Flows:**
- **Incoming**: Store Data (create, update, and delete operations)
- **Outgoing**: Retrieve Data (query operations for reading information)

### System Interactions

The context diagram reveals that the EnrollAssess system serves as a central hub connecting five external entities. Data flows bidirectionally between the system and each entity, enabling complete information exchange. Applicants submit their credentials and receive examination materials, while administrators inject control signals and extract analytical reports. Instructors contribute evaluation data and receive assignment details, while the email system and database provide supporting infrastructure services.

---

## Level 1 Data Flow Diagram (Detailed Processes)

### Purpose

The Level 1 DFD decomposes the EnrollAssess system into six major processes, revealing the internal architecture and data flow patterns. This diagram exposes how different modules collaborate to achieve system objectives and demonstrates the separation of concerns in the system design.

### Processes

#### Process 1.0: Authentication

**Function**: Login & Access Control

The authentication process manages user identity verification and session management. It implements role-based access control (RBAC) to enforce authorization policies. The process validates access codes for applicants, authenticates administrative users through username/password combinations, and creates secure sessions upon successful verification.

**Input Data Flows:**
- Access codes from applicants
- Login credentials from administrators and instructors

**Output Data Flows:**
- Login status confirmations
- Session tokens
- Access denial messages for invalid credentials

**Data Store Interactions:**
- **D1: Users** - Reads user account information to verify credentials, writes session data

**Process Logic:**

The authentication module applies rate limiting to prevent brute-force attacks, limiting applicant login attempts to 10 per minute and administrative logins to 5 per minute. It hashes passwords using bcrypt with 12 rounds and validates access codes against expiration timestamps. Upon successful authentication, the process establishes encrypted sessions stored in Redis for fast retrieval.

#### Process 2.0: Exam Management

**Function**: Create & Conduct Exams

The exam management process handles all examination-related operations. It allows administrators to construct exams by selecting questions from the question bank, configures examination parameters (time limits, passing scores, weighting schemes), and presents examinations to authenticated applicants. The process implements auto-save functionality to prevent data loss and applies randomization algorithms to reduce cheating opportunities.

**Input Data Flows:**
- Examination configurations from administrators
- Exam responses from applicants

**Output Data Flows:**
- Examination interfaces delivered to applicants
- Calculated scores and performance analytics
- Exam completion confirmations

**Data Store Interactions:**
- **D2: Exams & Results** - Reads question data, writes applicant responses and calculated scores

**Process Logic:**

The system retrieves questions from the database based on administrator-defined selection criteria, applies randomization to question order, and presents them through a web interface with countdown timers. As applicants submit answers, the process validates responses, calculates scores using predefined answer keys, applies weighted scoring formulas, and persists results to the database. The scoring algorithm weights examination performance at 60% of the total admission score.

#### Process 3.0: Applicant Management

**Function**: Track & Manage Records

The applicant management process maintains comprehensive applicant profiles throughout the admission lifecycle. It stores demographic information, educational backgrounds, contact details, examination results, interview scores, and final admission decisions. The process provides administrators with filtering, sorting, and search capabilities to efficiently manage large applicant pools.

**Input Data Flows:**
- Create/update/delete commands from administrators
- Profile information from applicants during exam registration

**Output Data Flows:**
- Applicant lists with current status indicators
- Detailed applicant profiles
- Search and filter results

**Data Store Interactions:**
- **D3: Applicants** - Comprehensive read and write access to applicant records

**Process Logic:**

The process enforces data validation rules on all applicant information, ensuring required fields contain appropriate values. It maintains status flags (Registered, Exam Scheduled, Exam Completed, Interview Assigned, Interview Completed, Admitted, Rejected) that track applicant progress. The module aggregates scores from multiple sources (exam results, interview evaluations) to calculate composite rankings that inform admission decisions.

#### Process 4.0: Interview Assignment

**Function**: Schedule & Evaluate

The interview assignment process coordinates the interview stage of the admission workflow. Administrators assign qualified applicants to instructors, set evaluation deadlines, and monitor completion rates. Instructors access their assigned applicants, view portfolios including examination performance, conduct interviews using standardized rubrics, and submit scores through evaluation forms.

**Input Data Flows:**
- Assignment requests from administrators
- Interview scores and qualitative feedback from instructors

**Output Data Flows:**
- Interview assignments delivered to instructors
- Interview status updates sent to administrators
- Real-time notifications via Pusher

**Data Store Interactions:**
- **D3: Applicants** - Updates applicant records with interview scores and status changes

**Process Logic:**

The process creates interview records that link applicants to instructors, establishing relationships in the database. It validates that only applicants with passing examination scores receive interview assignments. When instructors submit evaluations, the process applies weighted scoring (interview scores contribute 10% to final admission scores) and updates applicant status fields. The module triggers notification events when interviews are assigned or completed.

#### Process 5.0: Report Generation

**Function**: Analytics & Export

The report generation process produces analytical summaries and exports data in multiple formats. It aggregates information from all data stores to create comprehensive reports including EVSU Results (XLSX and PDF), Qualifiers Lists (DOCX), and statistical analyses. The process executes as background jobs to prevent blocking interactive operations.

**Input Data Flows:**
- Report requests from administrators specifying filters (program, date range, status)

**Output Data Flows:**
- Generated reports in XLSX, PDF, and DOCX formats
- Real-time completion notifications

**Data Store Interactions:**
- **D1: Users** - Reads user information for report metadata
- **D2: Exams & Results** - Reads examination scores and statistics
- **D3: Applicants** - Reads applicant profiles and admission decisions

**Process Logic:**

The process queries databases using administrator-specified filters, extracts relevant data, and populates report templates. For PDF generation, it creates XLSX files using PhpSpreadsheet, then invokes LibreOffice in headless mode to convert spreadsheets to PDF format. The module stores generated files in secure storage directories and records metadata in the generated_reports table. Upon completion, it dispatches Pusher events to notify administrators that reports are ready for download.

#### Process 6.0: Notification Service

**Function**: Email & Alerts

The notification service manages all system communications with users. It dispatches emails for access code distribution, examination invitations, interview schedules, and status updates. The process queues notification jobs to Redis, allowing asynchronous processing that prevents email delays from impacting system responsiveness.

**Input Data Flows:**
- Notification trigger events from other processes
- Email templates with dynamic content

**Output Data Flows:**
- Formatted email messages sent to Email System
- Delivery status confirmations

**Data Store Interactions:**
- Reads user contact information from all data stores to determine recipients

**Process Logic:**

The process listens for system events (exam completion, interview assignment, admission decisions) that trigger notification requirements. It retrieves recipient email addresses from the database, populates email templates with personalized content, and submits jobs to the queue system. Worker processes consume jobs from the queue and invoke external email services. The module logs delivery outcomes and implements retry logic for failed transmissions.

### Data Stores

#### D1: Users

**Description**: User accounts & roles

The Users data store maintains information about all system users including administrators, department heads, and instructors. Each record contains authentication credentials (hashed passwords), role assignments, profile information, and account status flags.

**Schema Components:**
- User identification (ID, username, email)
- Authentication data (password hashes, remember tokens)
- Role assignments (Administrator, Department Head, Instructor)
- Profile details (name, department, contact information)
- Timestamps (created_at, updated_at)

**Accessing Processes:**
- **Process 1.0 (Authentication)**: Validates login credentials and manages sessions
- **Process 5.0 (Report Generation)**: Retrieves user information for audit trails

#### D2: Exams & Results

**Description**: Questions, answers, scores

The Exams & Results data store contains all examination-related data including question banks, exam configurations, applicant responses, and calculated scores.

**Schema Components:**
- Question definitions (text, options, correct answers, categories, difficulty levels)
- Exam configurations (active exam, section definitions, time limits, scoring weights)
- Access codes (unique identifiers, expiration dates, usage status)
- Applicant responses (submitted answers, timestamps)
- Calculated scores (raw scores, weighted scores, performance analytics)

**Accessing Processes:**
- **Process 2.0 (Exam Management)**: Creates exams, records responses, calculates scores
- **Process 5.0 (Report Generation)**: Extracts examination statistics for reports

#### D3: Applicants

**Description**: Personal info & interviews

The Applicants data store maintains comprehensive profiles for all individuals applying to the university.

**Schema Components:**
- Personal information (name, date of birth, gender, contact details)
- Educational background (previous schools, grades, educational track)
- Examination results (scores, completion timestamps, performance metrics)
- Interview data (assigned instructors, scheduled dates, evaluation scores, feedback)
- Admission status (current stage, final decisions, enrollment confirmation)
- Aggregate scores (composite rankings combining exam and interview performance)

**Accessing Processes:**
- **Process 3.0 (Applicant Management)**: Full CRUD operations on applicant records
- **Process 4.0 (Interview Assignment)**: Updates interview scores and status fields
- **Process 5.0 (Report Generation)**: Queries applicant data for reports

### Data Flow Patterns

The Level 1 diagram reveals several important architectural patterns:

**1. Layered Data Access**

All processes interact with data stores through well-defined interfaces, preventing direct database manipulation by external entities. This layering provides abstraction that simplifies maintenance and enhances security.

**2. Event-Driven Notifications**

Multiple processes trigger the notification service when significant events occur, implementing a publisher-subscriber pattern that decouples notification logic from business processes.

**3. Centralized Authentication**

A single authentication process validates all system access, ensuring consistent security policy enforcement and simplified credential management.

**4. Asynchronous Processing**

The report generation and notification processes execute asynchronously, preventing long-running operations from degrading user experience during interactive sessions.

**5. Data Flow Symmetry**

Most process-datastore interactions exhibit bidirectional data flows (read and write), indicating that processes both consume and produce persistent data rather than merely transforming transient information.

---

## System Architecture Characteristics

### Scalability

The modular process structure supports horizontal scaling. Each process can execute on separate application servers, with shared data stores providing coordination. The authentication process can scale independently to handle high login volumes during examination periods. Report generation processes can execute on dedicated worker servers to isolate resource-intensive operations.

### Security

Multiple security layers protect system integrity:

- **Authentication Process**: Enforces rate limiting and secure credential validation
- **Data Stores**: Implement access controls and encryption for sensitive data
- **Data Flows**: All communications use encrypted channels (HTTPS)
- **External Systems**: Email and database connections require authentication

### Maintainability

The clear separation of concerns simplifies system maintenance. Each process encapsulates specific functionality, allowing developers to modify individual modules without affecting others. The standardized data flow notation provides documentation that facilitates knowledge transfer to new team members.

### Reliability

The system implements multiple reliability mechanisms:

- **Auto-save**: Exam management process prevents data loss during examinations
- **Queue System**: Notification and report processes use persistent queues that survive system restarts
- **Transaction Management**: Data store operations use database transactions to maintain consistency
- **Error Handling**: Each process implements error detection and logging

---

## Data Flow Sequences

### Applicant Examination Workflow

1. Applicant provides access code to Authentication Process (1.0)
2. Authentication Process validates code against Users data store (D1)
3. Authentication Process returns login status to Applicant
4. Applicant requests examination from Exam Management Process (2.0)
5. Exam Management Process retrieves questions from Exams & Results data store (D2)
6. Exam Management Process delivers examination interface to Applicant
7. Applicant submits answers to Exam Management Process (2.0)
8. Exam Management Process calculates scores and stores results in data store (D2)
9. Exam Management Process updates applicant status in Applicants data store (D3)
10. Exam Management Process triggers Notification Service (6.0)
11. Notification Service sends confirmation email via Email System

### Interview Assignment Workflow

1. Admin sends assignment request to Interview Assignment Process (4.0)
2. Interview Assignment Process validates applicant eligibility from Applicants data store (D3)
3. Interview Assignment Process creates interview records in data store (D3)
4. Interview Assignment Process triggers Notification Service (6.0)
5. Notification Service sends assignment emails to instructors via Email System
6. Interview Assignment Process delivers assignment details to Instructors
7. Instructors submit evaluation scores to Interview Assignment Process (4.0)
8. Interview Assignment Process updates applicant records in data store (D3)
9. Interview Assignment Process sends status update to Admin

### Report Generation Workflow

1. Admin sends report request to Report Generation Process (5.0)
2. Report Generation Process queries Users data store (D1) for metadata
3. Report Generation Process queries Exams & Results data store (D2) for scores
4. Report Generation Process queries Applicants data store (D3) for profiles
5. Report Generation Process aggregates data and generates formatted files
6. Report Generation Process stores files in secure storage
7. Report Generation Process triggers Notification Service (6.0)
8. Notification Service sends completion notification to Admin
9. Report Generation Process delivers download link to Admin

---

## Conclusion

The data flow diagrams presented in this document illustrate the EnrollAssess system's architecture across two levels of abstraction. The Level 0 diagram establishes the system boundary and external interactions, while the Level 1 diagram reveals internal process decomposition and data flows. Together, these diagrams demonstrate a well-structured system that implements clear separation of concerns, supports secure multi-user access, maintains comprehensive data persistence, and provides robust notification and reporting capabilities. The modular architecture facilitates maintenance, supports scalability, and ensures that the system effectively serves the needs of universities managing complex admission processes.
