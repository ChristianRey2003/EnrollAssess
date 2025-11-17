<?php

namespace App\Data;

class PhilippineLocations
{
    /**
     * Get all provinces in the Philippines.
     */
    public static function provinces(): array
    {
        return [
            'Abra',
            'Agusan del Norte',
            'Agusan del Sur',
            'Aklan',
            'Albay',
            'Antique',
            'Apayao',
            'Aurora',
            'Basilan',
            'Bataan',
            'Batanes',
            'Batangas',
            'Benguet',
            'Biliran',
            'Bohol',
            'Bukidnon',
            'Bulacan',
            'Cagayan',
            'Camarines Norte',
            'Camarines Sur',
            'Camiguin',
            'Capiz',
            'Catanduanes',
            'Cavite',
            'Cebu',
            'Cotabato',
            'Davao de Oro',
            'Davao del Norte',
            'Davao del Sur',
            'Davao Occidental',
            'Davao Oriental',
            'Dinagat Islands',
            'Eastern Samar',
            'Guimaras',
            'Ifugao',
            'Ilocos Norte',
            'Ilocos Sur',
            'Iloilo',
            'Isabela',
            'Kalinga',
            'La Union',
            'Laguna',
            'Lanao del Norte',
            'Lanao del Sur',
            'Leyte',
            'Maguindanao',
            'Marinduque',
            'Masbate',
            'Metro Manila',
            'Misamis Occidental',
            'Misamis Oriental',
            'Mountain Province',
            'Negros Occidental',
            'Negros Oriental',
            'Northern Samar',
            'Nueva Ecija',
            'Nueva Vizcaya',
            'Occidental Mindoro',
            'Oriental Mindoro',
            'Palawan',
            'Pampanga',
            'Pangasinan',
            'Quezon',
            'Quirino',
            'Rizal',
            'Romblon',
            'Samar',
            'Sarangani',
            'Siquijor',
            'Sorsogon',
            'South Cotabato',
            'Southern Leyte',
            'Sultan Kudarat',
            'Sulu',
            'Surigao del Norte',
            'Surigao del Sur',
            'Tarlac',
            'Tawi-Tawi',
            'Zambales',
            'Zamboanga del Norte',
            'Zamboanga del Sur',
            'Zamboanga Sibugay',
        ];
    }

    /**
     * Get cities/municipalities grouped by province.
     */
    public static function citiesByProvince(): array
    {
        return [
            'Leyte' => [
                'Abuyog',
                'Alangalang',
                'Albuera',
                'Babatngon',
                'Barugo',
                'Bato',
                'Baybay City',
                'Burauen',
                'Calubian',
                'Capoocan',
                'Carigara',
                'Dagami',
                'Dulag',
                'Hilongos',
                'Hindang',
                'Inopacan',
                'Isabel',
                'Jaro',
                'Javier',
                'Julita',
                'Kananga',
                'La Paz',
                'Leyte',
                'MacArthur',
                'Mahaplag',
                'Matag-ob',
                'Matalom',
                'Mayorga',
                'Merida',
                'Ormoc City',
                'Palo',
                'Palompon',
                'Pastrana',
                'San Isidro',
                'San Miguel',
                'Santa Fe',
                'Tabango',
                'Tabontabon',
                'Tanauan',
                'Tolosa',
                'Tunga',
                'Villaba',
            ],
            'Metro Manila' => [
                'Caloocan City',
                'Las Pinas City',
                'Makati City',
                'Malabon City',
                'Mandaluyong City',
                'Manila',
                'Marikina City',
                'Muntinlupa City',
                'Navotas City',
                'Paranaque City',
                'Pasay City',
                'Pasig City',
                'Quezon City',
                'San Juan City',
                'Taguig City',
                'Valenzuela City',
                'Pateros',
            ],
            'Cebu' => [
                'Alcantara',
                'Alcoy',
                'Alegria',
                'Aloguinsan',
                'Argao',
                'Asturias',
                'Badian',
                'Balamban',
                'Bantayan',
                'Barili',
                'Bogo City',
                'Boljoon',
                'Borbon',
                'Carcar City',
                'Carmen',
                'Catmon',
                'Cebu City',
                'Compostela',
                'Consolacion',
                'Cordova',
                'Daanbantayan',
                'Dalaguete',
                'Danao City',
                'Dumanjug',
                'Ginatilan',
                'Lapu-Lapu City',
                'Liloan',
                'Madridejos',
                'Malabuyoc',
                'Mandaue City',
                'Medellin',
                'Minglanilla',
                'Moalboal',
                'Naga City',
                'Oslob',
                'Pilar',
                'Pinamungajan',
                'Poro',
                'Ronda',
                'Samboan',
                'San Fernando',
                'San Francisco',
                'San Remigio',
                'Santa Fe',
                'Santander',
                'Sibonga',
                'Sogod',
                'Tabogon',
                'Tabuelan',
                'Talisay City',
                'Toledo City',
                'Tuburan',
                'Tudela',
            ],
            // Add more provinces and their cities as needed
            // For now, we'll have a fallback for other provinces
        ];
    }

    /**
     * Get cities for a specific province.
     */
    public static function getCitiesForProvince(string $province): array
    {
        $cities = self::citiesByProvince();
        return $cities[$province] ?? [];
    }

    /**
     * Validate if city belongs to province.
     */
    public static function isValidCityProvinceCombo(string $city, string $province): bool
    {
        $cities = self::getCitiesForProvince($province);
        
        // If province not in our list, allow any city (for flexibility)
        if (empty($cities)) {
            return true;
        }
        
        return in_array($city, $cities);
    }

    /**
     * Get sex options.
     */
    public static function sexOptions(): array
    {
        return [
            'Male',
            'Female',
            'Other',
        ];
    }

    /**
     * Get civil status options.
     */
    public static function civilStatusOptions(): array
    {
        return [
            'Single',
            'Married',
            'Widowed',
            'Separated',
            'Divorced',
        ];
    }

    /**
     * Get senior high school strand options.
     */
    public static function strandOptions(): array
    {
        return [
            'ABM' => 'Accountancy, Business and Management',
            'STEM' => 'Science, Technology, Engineering and Mathematics',
            'HUMSS' => 'Humanities and Social Sciences',
            'TVL' => 'Technical-Vocational-Livelihood',
            'Others' => 'Others',
        ];
    }

    /**
     * Get applicant type options.
     */
    public static function applicantTypeOptions(): array
    {
        return [
            'New College Applicant',
            'Transferee',
            'ALS passer',
        ];
    }

    /**
     * Get PWD status options.
     */
    public static function pwdStatusOptions(): array
    {
        return [
            'Yes',
            'No',
            'Prefer not to answer',
        ];
    }
}

