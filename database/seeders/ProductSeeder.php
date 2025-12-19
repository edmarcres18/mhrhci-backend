<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Principal;
use App\ProductType;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $principalIds = Principal::pluck('id')->toArray();
        if (count($principalIds) === 0) {
            $principalIds = [Principal::create([
                'name' => 'Default Principal',
                'description' => 'Default principal for seeded products.',
            ])->id];
        }

        $principalFor = function (int $index) use ($principalIds) {
            return $principalIds[$index % count($principalIds)];
        };

        $products = [
            // Medical Supplies
            [
                'name' => 'Premium N95 Respirator Masks - Box of 20',
                'product_type' => ProductType::MEDICAL_SUPPLIES,
                'description' => 'NIOSH-approved N95 respirator masks providing superior respiratory protection against airborne particles, bacteria, and viruses. These high-quality masks filter at least 95% of airborne particles and are essential for healthcare workers in high-risk environments. Each mask features adjustable nose clips and elastic ear loops for secure, comfortable fit during extended wear. Manufactured in compliance with FDA regulations and CDC guidelines. Ideal for hospitals, clinics, and healthcare facilities requiring reliable respiratory protection. Individual packaging ensures hygiene and convenience.',
                'images' => [],
                'features' => [
                    'NIOSH N95 certified with 95% filtration efficiency',
                    'FDA approved and CDC compliant',
                    'Adjustable aluminum nose clip for secure seal',
                    'Soft elastic ear loops for comfortable extended wear',
                    'Lightweight design reduces facial pressure',
                    'Individual hygienic packaging',
                    'Suitable for healthcare and industrial use',
                    'Box contains 20 individually wrapped masks',
                    'Long shelf life of 5 years when stored properly',
                    'Compatible with face shields and protective eyewear',
                ],
            ],
            [
                'name' => 'Sterile Nitrile Examination Gloves - 100 Pairs',
                'product_type' => ProductType::MEDICAL_SUPPLIES,
                'description' => 'Premium medical-grade nitrile examination gloves offering superior protection and tactile sensitivity for healthcare professionals. These powder-free, latex-free gloves are ideal for medical examinations, procedures, and laboratory work. Manufactured from high-quality nitrile material that provides excellent puncture resistance and chemical protection. Textured fingertips enhance grip in both wet and dry conditions. Ambidextrous design with beaded cuffs prevents roll-down and ensures easy donning. Suitable for users with latex allergies. Meets ASTM D6319 standards for medical examination gloves.',
                'images' => [],
                'features' => [
                    'Medical-grade nitrile material',
                    'Powder-free and latex-free formulation',
                    'Excellent puncture and tear resistance',
                    'Textured fingertips for enhanced grip',
                    'Ambidextrous design fits either hand',
                    'Beaded cuff prevents roll-down',
                    'Suitable for latex-sensitive individuals',
                    'ASTM D6319 compliant',
                    'Available in multiple sizes (S, M, L, XL)',
                    'Box of 100 gloves (50 pairs)',
                ],
            ],
            [
                'name' => 'Advanced Hydrocolloid Wound Dressing - 10cm x 10cm (Box of 10)',
                'product_type' => ProductType::MEDICAL_SUPPLIES,
                'description' => 'Advanced hydrocolloid wound dressings designed for optimal wound healing and patient comfort. These sterile, self-adhesive dressings create a moist healing environment that promotes autolytic debridement and accelerates tissue regeneration. The hydrocolloid technology absorbs wound exudate while maintaining moisture balance, preventing maceration and reducing the risk of infection. Transparent border allows for easy wound monitoring without removal. Waterproof and bacteria-proof outer layer protects the wound from external contamination while allowing the skin to breathe. Suitable for pressure ulcers, diabetic foot ulcers, leg ulcers, minor burns, and post-operative wounds.',
                'images' => [],
                'features' => [
                    'Creates optimal moist wound healing environment',
                    'Promotes autolytic debridement',
                    'Absorbs moderate wound exudate',
                    'Waterproof and bacteria-proof barrier',
                    'Transparent border for wound visualization',
                    'Gentle adhesive minimizes trauma upon removal',
                    'Flexible and conformable to body contours',
                    'Can remain in place for up to 7 days',
                    'Sterile and individually wrapped',
                    'Suitable for various wound types',
                ],
            ],
            [
                'name' => 'Disposable Surgical Face Masks - Type IIR (Box of 50)',
                'product_type' => ProductType::MEDICAL_SUPPLIES,
                'description' => 'High-quality Type IIR surgical face masks providing excellent bacterial filtration and splash resistance for medical procedures. These three-layer masks feature a non-woven outer layer, melt-blown filter middle layer, and soft non-woven inner layer for optimal protection and comfort. The adjustable nose bridge ensures a secure fit, while the high breathability reduces heat buildup during extended wear. Fluid resistant up to 120 mmHg, making them ideal for surgical procedures and patient care. Compliant with EN 14683 Type IIR standards. Hypoallergenic and latex-free. Essential for infection control in hospitals, clinics, dental offices, and healthcare facilities.',
                'images' => [],
                'features' => [
                    'EN 14683 Type IIR certified',
                    'Bacterial filtration efficiency (BFE) ≥98%',
                    'Splash resistant up to 120 mmHg',
                    'Three-layer protective construction',
                    'Adjustable aluminum nose bridge',
                    'High breathability for comfort',
                    'Elastic ear loops for secure fit',
                    'Hypoallergenic and latex-free',
                    'Individual or bulk packaging available',
                    'Box contains 50 masks',
                ],
            ],
            [
                'name' => 'Sterile Gauze Sponges - 10cm x 10cm (Pack of 100)',
                'product_type' => ProductType::MEDICAL_SUPPLIES,
                'description' => 'Premium sterile gauze sponges made from 100% cotton, essential for wound care, surgical procedures, and medical examinations. These highly absorbent, lint-free sponges are woven with 12-ply construction for superior strength and absorbency. Each sponge features a non-raveling edge to minimize lint and fiber residue in wounds. Individually wrapped in sterile packaging to ensure hygiene and convenience. Suitable for wound cleaning, dressing changes, applying medications, and absorbing blood and fluids during procedures. Meet USP standards for absorbency and are X-ray detectable for surgical use. A must-have item for any medical facility, clinic, or first-aid kit.',
                'images' => [],
                'features' => [
                    '100% pure cotton construction',
                    '12-ply for maximum absorbency',
                    'Sterile and individually wrapped',
                    'Non-raveling edges reduce lint',
                    'X-ray detectable for surgical safety',
                    'Highly absorbent for fluid management',
                    'Soft and gentle on sensitive skin',
                    'USP compliant for quality assurance',
                    '10cm x 10cm standard size',
                    'Pack contains 100 sterile sponges',
                ],
            ],
            [
                'name' => 'Alcohol-Based Hand Sanitizer - 70% Ethyl Alcohol (500ml)',
                'product_type' => ProductType::MEDICAL_SUPPLIES,
                'description' => 'Medical-grade hand sanitizer containing 70% ethyl alcohol, providing effective protection against bacteria, viruses, and other pathogens. This fast-acting formula kills 99.99% of germs in seconds without requiring water or rinsing. Enriched with moisturizing agents including glycerin and aloe vera to prevent skin dryness and irritation with frequent use. Quick-drying and non-sticky formula leaves hands feeling clean and refreshed. FDA-compliant formulation suitable for healthcare settings, food service, and general use. Large 500ml pump bottle ideal for clinical areas, patient rooms, and high-traffic locations. Essential for hand hygiene compliance and infection prevention protocols.',
                'images' => [],
                'features' => [
                    '70% ethyl alcohol content for optimal efficacy',
                    'Kills 99.99% of germs and pathogens',
                    'FDA-compliant formulation',
                    'Enriched with glycerin and aloe vera',
                    'Quick-drying and non-sticky',
                    'No water or rinsing required',
                    'Pleasant, non-offensive scent',
                    'Convenient pump dispenser',
                    '500ml volume for extended use',
                    'Suitable for frequent hand hygiene',
                ],
            ],
            [
                'name' => 'Disposable Isolation Gowns - Level 2 (Pack of 10)',
                'product_type' => ProductType::MEDICAL_SUPPLIES,
                'description' => 'AAMI Level 2 disposable isolation gowns providing moderate barrier protection for healthcare workers during patient care and medical procedures. These full-coverage gowns are made from breathable, fluid-resistant non-woven material that offers comfort during extended wear while maintaining protective barriers. Features long sleeves with elastic or knit cuffs, back closure with neck and waist ties, and full-length coverage to mid-calf. Suitable for use in emergency rooms, patient care units, laboratories, and moderate-risk procedures. Compliant with AAMI PB70 Level 2 standards. Disposable design ensures hygiene and eliminates cross-contamination risks.',
                'images' => [],
                'features' => [
                    'AAMI Level 2 barrier protection',
                    'Fluid-resistant non-woven material',
                    'Full-coverage design to mid-calf',
                    'Long sleeves with elastic cuffs',
                    'Back closure with neck and waist ties',
                    'Breathable for wearer comfort',
                    'Lightweight and easy to don',
                    'Disposable for infection control',
                    'Universal size fits most users',
                    'Pack contains 10 gowns',
                ],
            ],
            [
                'name' => 'Adhesive Wound Closure Strips - Sterile (Pack of 50)',
                'product_type' => ProductType::MEDICAL_SUPPLIES,
                'description' => 'Sterile adhesive wound closure strips providing non-invasive wound closure for minor lacerations, surgical incisions, and traumatic wounds. These reinforced strips offer a less painful alternative to traditional sutures or staples, reducing tissue trauma and minimizing scarring. The porous design allows air and moisture vapor to escape, promoting faster healing while maintaining wound edge approximation. Strong adhesive bonds securely to skin yet removes easily without residue. Can be used alone for minor wounds or in conjunction with subcuticular sutures for additional support. Ideal for emergency departments, urgent care clinics, and outpatient settings. Available in multiple sizes to accommodate various wound configurations.',
                'images' => [],
                'features' => [
                    'Non-invasive wound closure alternative',
                    'Reduces tissue trauma and scarring',
                    'Strong adhesive for secure wound edge approximation',
                    'Porous design promotes healing',
                    'Sterile and individually packaged',
                    'Easy to apply and remove',
                    'Leaves minimal adhesive residue',
                    'Can be used with or without sutures',
                    'Available in multiple sizes (6mm x 75mm)',
                    'Pack contains 50 strips',
                ],
            ],

            // Medical Equipment
            [
                'name' => 'Digital Blood Pressure Monitor - Automatic Upper Arm Type',
                'product_type' => ProductType::MEDICAL_EQUIPMENT,
                'description' => 'Professional-grade automatic digital blood pressure monitor designed for accurate and reliable blood pressure measurements in clinical and home settings. Features advanced oscillometric technology with intelligent inflation and deflation for comfortable, precise readings. Large LCD display shows systolic pressure, diastolic pressure, pulse rate, and irregular heartbeat detection. Memory function stores up to 120 readings for multiple users, enabling trend tracking and monitoring. Universal adult cuff (22-42cm) accommodates most arm sizes. Clinically validated for accuracy according to ESH and AAMI protocols. Battery or AC adapter powered for flexibility. Essential equipment for hospitals, clinics, pharmacies, and home health monitoring.',
                'images' => [],
                'features' => [
                    'Advanced oscillometric measurement technology',
                    'Clinically validated accuracy (ESH/AAMI)',
                    'Large, easy-to-read LCD display',
                    'Irregular heartbeat detection',
                    'Memory storage for 120 readings',
                    'Multiple user profiles',
                    'Universal adult cuff (22-42cm)',
                    'One-touch automatic operation',
                    'Battery or AC adapter powered',
                    'Includes carrying case and instruction manual',
                ],
            ],
            [
                'name' => 'Infrared Forehead Thermometer - Non-Contact',
                'product_type' => ProductType::MEDICAL_EQUIPMENT,
                'description' => 'Advanced non-contact infrared thermometer providing fast, accurate temperature measurements without physical contact, reducing cross-contamination risk. Measures body temperature from 3-5cm distance within 1 second using infrared technology. Large LED display with color-coded fever alarm (green for normal, yellow for slight fever, red for high fever). Can also measure object and room temperature. Memory function stores last 32 readings. Auto shut-off conserves battery life. Ideal for screening in hospitals, clinics, schools, offices, and public spaces. Medical-grade accuracy ±0.2°C. Suitable for infants, children, and adults. Battery operated with low battery indicator.',
                'images' => [],
                'features' => [
                    'Non-contact infrared measurement',
                    'Measures temperature in 1 second',
                    'Measurement distance: 3-5cm',
                    'Medical-grade accuracy: ±0.2°C',
                    'Large LED display with backlight',
                    'Color-coded fever alarm system',
                    'Stores 32 temperature readings',
                    'Body, object, and room temperature modes',
                    'Silent mode for sleeping patients',
                    'Auto shut-off after 30 seconds',
                ],
            ],
            [
                'name' => 'Pulse Oximeter - Fingertip Type with OLED Display',
                'product_type' => ProductType::MEDICAL_EQUIPMENT,
                'description' => 'Portable fingertip pulse oximeter providing accurate, real-time monitoring of blood oxygen saturation (SpO2) and pulse rate. Features bright OLED display with six adjustable viewing angles and multiple display modes for optimal visibility in any lighting condition. Advanced signal processing technology ensures reliable readings even with weak perfusion or patient movement. Measures SpO2 range from 0-100% with ±2% accuracy and pulse rate from 30-250 BPM. Automatic power-off after 8 seconds of no signal conserves battery. Lightweight, compact design with lanyard for portability. Ideal for hospitals, clinics, home care, sports medicine, and aviation. Suitable for adults, children, and athletes.',
                'images' => [],
                'features' => [
                    'Accurate SpO2 and pulse rate measurement',
                    'Bright OLED display with 6 viewing modes',
                    'SpO2 accuracy: ±2% (70-100%)',
                    'Pulse rate accuracy: ±2 BPM',
                    'Adjustable display brightness',
                    'Low perfusion performance: ≤0.4%',
                    'Automatic power-off (8 seconds)',
                    'Battery powered (AAA x 2, included)',
                    'Compact and portable with lanyard',
                    'Suitable for ages 12 and up',
                ],
            ],
            [
                'name' => 'Stethoscope - Dual Head Professional Grade',
                'product_type' => ProductType::MEDICAL_EQUIPMENT,
                'description' => 'Premium dual-head stethoscope designed for superior acoustic performance and durability. Features both diaphragm and bell chestpieces for comprehensive auscultation of heart, lung, and other body sounds across all patient populations. High-quality stainless steel chestpiece provides excellent acoustic sensitivity and is cold-resistant for patient comfort. Latex-free dual-lumen tubing eliminates rubbing noise and provides superior sound transmission. Ergonomic binaural design with adjustable aluminum headset ensures comfortable fit. Soft-sealing eartips provide excellent acoustic seal and all-day wearing comfort. Non-chill rim on chestpiece. Suitable for physicians, nurses, EMTs, and medical students. Available in multiple colors. Includes spare eartips and diaphragm.',
                'images' => [],
                'features' => [
                    'Dual-head design (diaphragm and bell)',
                    'High-quality stainless steel chestpiece',
                    'Latex-free dual-lumen tubing',
                    'Superior acoustic sensitivity',
                    'Adjustable binaural aluminum headset',
                    'Soft-sealing eartips for comfort',
                    'Non-chill rim for patient comfort',
                    'Durable construction for long service life',
                    'Suitable for all patient populations',
                    'Includes spare parts and accessories',
                ],
            ],
            [
                'name' => 'Portable Suction Unit - Battery Powered Emergency Aspirator',
                'product_type' => ProductType::MEDICAL_EQUIPMENT,
                'description' => 'Portable battery-powered suction unit providing reliable emergency aspiration in pre-hospital, clinical, and home care settings. Delivers powerful suction with adjustable vacuum pressure from 0-550 mmHg (-73 kPa). High-capacity 1000ml collection canister with overflow protection prevents fluid backflow into pump. Rechargeable lithium battery provides up to 45 minutes of continuous operation. Lightweight, compact design with carrying handle for easy transport. AC/DC charging capability. Visual and audible alarms for low battery, canister full, and high temperature. Includes disposable suction catheter, connecting tubing, and bacterial filter. Ideal for emergency medical services, surgical centers, intensive care units, and home healthcare.',
                'images' => [],
                'features' => [
                    'Adjustable vacuum: 0-550 mmHg (-73 kPa)',
                    'Flow rate: ≥20 liters per minute',
                    '1000ml collection canister with overflow protection',
                    'Rechargeable lithium battery (45 min runtime)',
                    'AC/DC charging capability',
                    'Visual and audible alarm systems',
                    'Lightweight and portable (2.5kg)',
                    'Easy-to-clean and maintain',
                    'Includes bacterial filter and accessories',
                    'Suitable for emergency and clinical use',
                ],
            ],
            [
                'name' => 'Adjustable Height Examination Table with Paper Roll Holder',
                'product_type' => ProductType::MEDICAL_EQUIPMENT,
                'description' => 'Versatile manual examination table designed for patient comfort and clinical functionality. Features hydraulic height adjustment from 60-85cm for ergonomic working positions and easy patient access. Durable upholstered surface with high-density foam padding and easy-to-clean, antimicrobial vinyl covering. Backrest adjusts to multiple positions from flat to 80 degrees for various examination and treatment procedures. Built-in paper roll holder accommodates standard examination table paper. Sturdy steel frame construction supports up to 200kg patient weight. Non-slip rubber feet ensure stability. Ideal for general practices, specialty clinics, physical therapy, and sports medicine. Easy assembly with included tools and instructions.',
                'images' => [],
                'features' => [
                    'Hydraulic height adjustment (60-85cm)',
                    'Adjustable backrest (0-80 degrees)',
                    'High-density foam padding for comfort',
                    'Antimicrobial vinyl upholstery',
                    'Built-in paper roll holder',
                    'Sturdy steel frame construction',
                    'Patient weight capacity: 200kg',
                    'Non-slip rubber feet',
                    'Easy-to-clean surface',
                    'Dimensions: 190cm L x 65cm W',
                ],
            ],
            [
                'name' => 'Medical-Grade Autoclave Sterilizer - 18L Capacity',
                'product_type' => ProductType::MEDICAL_EQUIPMENT,
                'description' => 'Compact desktop autoclave sterilizer utilizing high-pressure steam for effective sterilization of medical instruments, surgical tools, and laboratory equipment. 18-liter chamber capacity accommodates multiple instrument trays. Three pre-programmed sterilization cycles (121°C for wrapped items, 134°C for unwrapped items, and drying cycle) ensure versatile application. Microprocessor control with digital display shows temperature, pressure, and cycle time. Safety features include over-pressure protection, over-temperature protection, low-water alert, and door safety lock. Fast cycle times reduce instrument turnover. Built-in printer option for documentation and quality assurance. Stainless steel chamber and body for durability and corrosion resistance. Essential for dental clinics, medical offices, veterinary practices, and research laboratories.',
                'images' => [],
                'features' => [
                    '18-liter chamber capacity',
                    'Three pre-programmed sterilization cycles',
                    'Temperature range: 105-134°C',
                    'Pressure: 0.14-0.22 MPa',
                    'Microprocessor control with LCD display',
                    'Multiple safety protection systems',
                    'Fast cycle times (25-45 minutes)',
                    'Optional built-in printer',
                    'Stainless steel construction',
                    'Includes sterilization trays and accessories',
                ],
            ],
            [
                'name' => 'LED Surgical Examination Light - Mobile Stand with Intensity Control',
                'product_type' => ProductType::MEDICAL_EQUIPMENT,
                'description' => 'Professional LED examination light providing optimal illumination for medical examinations, minor procedures, and dental work. Features 36 high-intensity LED bulbs producing 80,000 lux illumination with natural daylight color temperature (4500K) for accurate tissue color representation. Stepless intensity adjustment from 10-100% accommodates various lighting needs. Flexible gooseneck arm allows precise positioning, while mobile stand with locking casters enables easy room-to-room transport. LED technology provides long lifespan (50,000 hours) with low heat generation and energy consumption. Shadow-free illumination with focused light pattern. Suitable for examination rooms, operating theaters, dental clinics, and outpatient facilities. Easy assembly and maintenance-free operation.',
                'images' => [],
                'features' => [
                    '36 high-intensity LED bulbs',
                    'Illumination: 80,000 lux at 0.5m',
                    'Color temperature: 4500K (daylight)',
                    'Stepless intensity adjustment (10-100%)',
                    'Flexible gooseneck arm (75cm)',
                    'Mobile stand with locking casters',
                    'LED lifespan: 50,000 hours',
                    'Low heat generation and energy consumption',
                    'Shadow-free focused illumination',
                    'Maintenance-free operation',
                ],
            ],
        ];

        foreach ($products as $index => $product) {
            $product['principal_id'] = $principalFor($index);
            Product::create($product);
        }
    }
}
