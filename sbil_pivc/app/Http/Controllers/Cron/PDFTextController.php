<?php

namespace App\Http\Controllers\Cron;

use App\Http\Controllers\Controller;

class PDFTextController extends Controller
{
    public static function handlePDFTextAllLang($journeyType, $slugName, $flowKey, $screen, $source, $loanCategory, $productName, $language, $dataListValue)
    {
        $lowerScreen = strtolower($screen);
        $screenKey = strtolower(str_replace(" ", "", $dataListValue['image']['screen'] ?? ''));
        $screenType = '';

        if ($journeyType == 'rinnraksha') {
            if (strpos($lowerScreen, 'welcomescreen') !== false) {
                $screenType = 'welcome';
            } elseif (strpos($lowerScreen, 'personaldetails') !== false) {
                $screenType = 'personal';
            } elseif (strpos($lowerScreen, 'medicalquestionnaire') !== false) {
                $screenType = 'medical';
            } elseif (strpos($lowerScreen, 'medicalconfirmationscreenone') !== false) {
                $screenType = 'confirm1';
            } elseif (strpos($lowerScreen, 'medicalconfirmationscreentwo') !== false) {
                $screenType = 'confirm2';
            } elseif (strpos($screenKey, 'medicalquestionnaire-disagree') !== false) {
                $screenType = 'disagree';
            }
          
          
            switch ($screenType) {

                case 'welcome':
                    switch ($language) {
                        case 'hin':
                            $loan_type_rinn = ($loanCategory == "Home Loan") ? "होम लोन" : "पर्सनल लोन";
                            return "एसबीआई लाइफ को अपने पसंदीदा लाइफ इंश्योरेंस पार्टनर के रूप में चुनने के लिए आपका धन्यवाद. आप द्वारा चुने गए एसबीआई लाइफ ऋणरक्षा $loan_type_rinn प्रस्ताव की प्री-इशुएंस वैरिफिकेशन प्रक्रिया में आपका स्वागत है. आपका फॉर्म नं. स्क्रीन पर प्रदर्शित किया गया है. आप हमारे साथ होने वाले भावी पत्राचारों के लिए इस फॉर्म नं. का संदर्भ दे सकते हैं.";
                        default:
                            return "Thank you for choosing SBI life as your preferred life insurance partner. Welcome to the pre-issuance verification process of Your SBI Life - $productName plan chosen by you, to protect your $loanCategory. Your Form number is displayed on the screen. You can quote this Form number for all future communications with us.";
        
                    }
                    
                case 'personal':
                    switch ($language) {
                        case 'hin':
                            return "कृपया स्क्रीन पर दिखाई गई व्यक्तिगत विवरणों को सत्यापित करें। कृपया ध्यान दें कि ये विवरण आपके प्रस्ताव के स्वीकार होने के बाद आपके बीमा प्रमाणपत्र का हिस्सा बनेंगे।";
                        default:
                            return "Please verify the personal details displayed on the screen. Please note these details will form part of your Certificate of Insurance after your proposal is accepted.";
                    }
                    
        
                case 'medical':
                    switch ($language) {
                        case 'hin':
                            return  "हम चाहते हैं कि आप इस बात की पुष्टि करें कि आपने प्रपोज़ल में दिए गए सभी चिकित्सा सम्बंधी प्रश्नों को सही ढंग से पढ़ा और उनका उत्तर दिया है और चिकित्सा/ उपचार के इतिहास (यदि कोई हो) से सम्बंधित सभी विवरणों का खुलासा किया है। [किसी भी प्रतिकूल चिकित्सा इतिहास के गैर-प्रकटीकरण से भविष्य में क्लेम की अस्वीकृति हो सकती है]";
                        default:
                            return "We would like you to confirm that you have read and answered all the medical questions in the proposal correctly and disclosed all details of medical/treatment history (if any). [Non-disclosure of any adverse medical history may lead to rejection of claim in future]";
                    }
                  
                case 'confirm1':
                    switch ($language) {
                        case 'hin':
                            $formattedText = [];
                            foreach ($dataListValue['response']['input'] as $key => $value) {
                                if (!empty($value)) {
                                    switch ($key) {
                                        case "medicalConditionPresent":
                                            $formattedText[] = "1.क्या वर्तमान में आप किसी मेडिकल स्थिति के लिए उपचार ले रहे हैं?<br>$value</br>";
                                            break;
                                        case "medicalConditionPresent_des":
                                            if (strtolower($dataListValue['response']['input']['medicalConditionPresent']) == 'yes') {
                                                $formattedText[] = "$value";
                                            }
                                            break;
                                        case "treatmentLast_5years":
                                            $formattedText[] = "2.क्या पिछले 5 वर्षों में आपको कभी अस्पताल में भर्ती या किसी बीमारी के लिए ऑपरेट या उपचार किया गया है?<br>$value</br>";
                                            break;
                                        case "treatmentLast_5years_des":
                                            if (strtolower($dataListValue['response']['input']['treatmentLast_5years']) == 'yes') {
                                                $formattedText[] = "$value";
                                            }
                                            break;
                                    }
                                }
                            }
                            return implode("<br>", $formattedText);
                        default:
                            $formattedText = [];
                            foreach ($dataListValue['response']['input'] as $key => $value) {
                                if (!empty($value)) {
                                    switch ($key) {
                                        case "medicalConditionPresent":
                                            $formattedText[] = "1. Are you undergoing treatment for any medical condition at present?<br>$value</br>";
                                            break;
                                        case "medicalConditionPresent_des":
                                            if (strtolower($dataListValue['response']['input']['medicalConditionPresent']) == 'yes') {
                                                $formattedText[] = "$value";
                                            }
                                            break;
                                        case "treatmentLast_5years":
                                            $formattedText[] = "2. Have you been Hospitalized or operated or underwent treatment for any ailment in last 5 years?<br>$value</br>";
                                            break;
                                        case "treatmentLast_5years_des":
                                            if (strtolower($dataListValue['response']['input']['treatmentLast_5years']) == 'yes') {
                                                $formattedText[] = "$value";
                                            }
                                            break;
                                    }
                                }
                            }
                            return implode("<br>", $formattedText);
                    }
                   
        
                case 'confirm2':
                    $formattedText = [];
                    $responseInput = $dataListValue['response']['input'];
        
                    foreach ($responseInput as $key => $value) {
                        /* if ($key === "reviewProposalResponse") {
                            $formattedText[] = "1. Do you wish to review your responses given in the proposal form for medical Questionnaire?<br>$value</br>";
                            if (strtolower($value) !== 'yes') break;
                            continue;
                        } */
                        if ($key === "reviewProposalResponse") {
                            $questionText = '';
                        
                            switch (strtolower($language)) {
                                case 'hin':
                                    $questionText = "1. क्या आप मेडिकल प्रश्नावली के लिए प्रस्ताव फॉर्म में दी गई प्रतिक्रियाओं की समीक्षा करना चाहते हैं?";
                                    break;
                              
                                case 'tamil':
                                    $questionText = "1. முன்மொழிவு படிவத்தில் கொடுக்கப்பட்ட பதில்களை மருத்துவ வினாத்தாளுக்காக மதிப்பாய்வு செய்ய விரும்புகிறீர்களா?";
                                    break;
                               
                                default:
                                    $questionText = "1. Do you wish to review your responses given in the proposal form for medical Questionnaire?";
                                    break;
                            }
                        
                            $formattedText[] = "$questionText<br>$value</br>";
                        
                            if (strtolower($value) !== 'yes') break;
                            continue;
                        }
                        
                        $questionMap = [
                            "str_rinn_have_you_consulted_any_doctor" => "2. Have you consulted any doctor for surgical operation or have been hospitalized for any disorder other than minor cough, cold or flu during the last 5 years?",
                            "str_rinn_have_you_any_illness_injury" => "3. Have you ever had any illness/injury, major surgical operation or received any treatment for any medical conditions for a continuous period of more than 14 days? (Except for minor cough, cold, flu, appendicitis & typhoid)",
                            "str_rinn_diabetes_raised" => "4. Have you ever suffered from/been treated /hospitalized for a diagnosed to have a) Diabetes, raised blood sugar or high blood pressure?",
                            "str_rinn_chest_pain" => "5. Chest pain, heart attack, heart disease or any other disorder of the circulatory sys Stroke, paralysis, disorder of the brain/nervous system?",
                            "str_rinn_hiv_infections" => "6. HIV infections, AIDS?",
                            "str_rinn_cancer_tumor" => "7. Cancer, tumor, growth or cyst of any kind?",
                            "str_rinn_kidney_disorder" => "8. Any genitourinary or kidney disorder, Hepatitis B/C or any other liver diseases?",
                            "str_rinn_digestive_disorder" => "9. Any digestive disorder (ulcer, colitis etc), any disease of the gall bladder, spleen, any blood disorder or any other gland (e.g. Thyroid etc) or any musculoskeletal disorder?",
                            "str_rinn_asthma" => "10. Asthma, Tuberculosis, Pneumonia, or any other disease of the lung?",
                            "str_rinn_mental_disorder" => "11. Mental, psychiatric or nervous disorder?",
                            "str_rinn_any_other_disease" => "12. Have you suffered from any other disease not mentioned above?",
                            "str_rinn_current_medication" => "13. Are you at present taking any medication, or on any special diet or on any treatment?",
                            "str_rinn_insurance_declined" => "14. Has a proposal for Life insurance, ever been declined, postponed, withdrawn or accepted at extra premium?",
                            "str_rinn_tests_advised_angiography" => "15. Have you had or have been advised to undergo any of the following tests or investigation?",
                            "str_rinn_smoking" => "16. Do you consume more than 10 Cigarettes/bidis per day or chew more than 5 pouches of tobacco per day?",
                            "str_rinn_alcohol_consumption" => "17. Do you consume more than 2 pegs of alcohol per day in any form?",
                            "str_rinn_alcohol_type" => "A. Type : ",
                            "str_rinn_alcohol_quantity" => "B. Quantity : ",
                            "str_rinn_narcotics_usage" => "18. Do you use or have you used any narcotics/any other drugs?",
                            "str_rinn_pregnant" => "19. FEMALE ENSURED ONLY: a) Are you pregnant?",
                            "str_rinn_months_pregnant" => "A. Months in Pregnant:",
                            "str_rinn_gynecological_problems" => "b) Have you suffered from any gynecological problems or illness related to breasts and uterus or ovary?"
                        ];
        
                        $questionText = $questionMap[$key] ?? '';
                        if (!$questionText) continue;
        
                        if (strpos(strtolower($value), '_edit') !== false) {
                            $questionText = "<b style='color:red'>" . $questionText . "</b>";
                            $val = str_replace("_EDIT", "", strtoupper($value));
                            $value = ($val == "TRUE") ? "<b style='color:red'>YES</b>" : (($val == "FALSE") ? "<b style='color:red'>NO</b>" : "<b style='color:red'>" . $val . "</b>");
                        } elseif ($value === true || $value === "true") {
                            $value = "YES";
                        }
        
                        if (in_array($key, ['str_rinn_alcohol_type', 'str_rinn_alcohol_quantity', 'str_rinn_months_pregnant'])) {
                            $formattedText[] = $questionText . $value;
                        } else {
                            $formattedText[] = $questionText . "<br>" . strtoupper($value) . "</br>";
                        }
                    }
        
                    return implode("<br>", $formattedText);
        
                case 'disagree':
                    return "Enter your disagreement in the box provided.";
        
                default:
                    return '';
            }
        } elseif ($journeyType == 'pivc') {
            // Add PIVC-specific screenType mapping and content here
            if (strpos($lowerScreen, 'welcome') !== false) {
                $screenType = 'welcome';
            }

            switch ($screenType) {
                case 'welcome':
                    return "Welcome to PIVC journey – language: $language";
                // Add other cases for PIVC
            }
        }

        return '';
    }
}
