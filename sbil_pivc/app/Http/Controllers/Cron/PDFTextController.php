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
            }elseif (strpos($lowerScreen, 'personaldetails-disagree') !== false) {
                $screenType = 'personal_disagree';
            } elseif (strpos($lowerScreen, 'medicalquestionnaire') !== false) {
                $screenType = 'medical';
            } elseif (strpos($lowerScreen, 'medicalconfirmationscreenone') !== false) {
                $screenType = 'confirm1';
            } elseif (strpos($lowerScreen, 'medicalconfirmationscreentwo') !== false) {
                $screenType = 'confirm2';
            } elseif (strpos($screenKey, 'medicalquestionnaire-disagree') !== false) {
                $screenType = 'medical_disagree';
            }

            
            switch ($screenType) {

                case 'welcome':
                    switch ($language) {
                        case 'hin':
                            $loan_type_rinn = ($loanCategory == "Home Loan") ? "होम लोन" : "पर्सनल लोन";
                            return "एसबीआई लाइफ को अपने पसंदीदा लाइफ इंश्योरेंस पार्टनर के रूप में चुनने के लिए आपका धन्यवाद. आप द्वारा चुने गए एसबीआई लाइफ ऋणरक्षा $loan_type_rinn प्रस्ताव की प्री-इशुएंस वैरिफिकेशन प्रक्रिया में आपका स्वागत है. आपका फॉर्म नं. स्क्रीन पर प्रदर्शित किया गया है. आप हमारे साथ होने वाले भावी पत्राचारों के लिए इस फॉर्म नं. का संदर्भ दे सकते हैं.";

                        case 'kan':
                            $loan_type_rinn = ($loanCategory == "Home Loan") ? "ಗೃಹ ಸಾಲ" : "ವೈಯಕ್ತಿಕ ಸಾಲ";
                            return "ಎಸ್‌ಬಿಐ ಲೈಫ್‌ ಅನ್ನು ನಿಮ್ಮ ಇಷ್ಟದ ಜೀವ ವಿಮಾ ಪಾರ್ಟ್‌ನರ್ ಆಗಿ ಆಯ್ದುಕೊಂಡಿದ್ದಕ್ಕಾಗಿ ನಿಮಗೆ ಧನ್ಯವಾದಗಳು. ನಿಮ್ಮ $loan_type_rinn ವನ್ನು ಸಂರಕ್ಷಿಸಲು ನಿಮ್ಮಿಂದ ಆಯ್ಕೆ ಮಾಡಲಾದ ನಿಮ್ಮ ಎಸ್ಬಿಐ ಲೈಫ್ - ಋಣ್ ರಕ್ಷಾಕ್ಕಾಗಿ ನೀಡಿಕೆಯ - ಮೊದಲಿನ ಪರಿಶೀಲನೆಯ ಪ್ರಕ್ರಿಯೆಗೆ ನಿಮಗೆ ಸ್ವಾಗತ, ನಿಮ್ಮ ಫಾರ್ಮ್ ನಂಬರನ್ನು ಸ್ಕ್ರೀನ್ ಮೇಲೆ ತೋರಿಸಲಾಗಿದೆ. ಈ ಮುಂದೆ ನಮ್ಮೊಂದಿಗೆ ಮಾಡುವ ಎಲ್ಲಾ ಸಂಪರ್ಕಗಳಲ್ಲೂ ನೀವು ಈ ನಂಬರನ್ನು ಉದ್ಧರಿಸಬಹುದು.";

                        case 'tel':
                            $loan_type_rinn = ($loanCategory == "Home Loan") ? "హోమ్ లోన్" : "పర్సనల్ లోన్";
                            return "మీకు నచ్చిన జీవిత బీమా భాగస్వామిగా ఎస్‌బిఐ లైఫ్‌ని ఎంచుకున్నందుకు ధన్యవాదాలు. మీ $loan_type_rinn భద్రత కోసం మీరు ఎంచుకున్న ఎస్బిఐ లైఫ్ -రిన్ రక్ష ప్లాన్ ముందుగా జారీ చేసే ధృవీకరణ ప్రక్రియకు స్వాగతం.  స్క్రీన్ పైన మీ ఫారం నంబర్ చూపించడం జరుగుతుంది. భవిష్యత్తులో మాతో వ్యవహారాల కోసం ఈ ఫారం నుంచి పేర్కొనవచ్చును.";

                        case 'mal':
                            $loan_type_rinn = ($loanCategory == "Home Loan") ? "ഹോം ലോൺ" : "പേഴ്സണൽ ലോൺ";
                            return "നിങ്ങളുടെ ഇഷ്ടപ്പെട്ട ലൈഫ് ഇൻഷുറൻസ് പങ്കാളിയായി എസ്ബിഐ ലൈഫിനെ തിരഞ്ഞെടുത്തതിന് നന്ദി. നിങ്ങളുടെ $loan_type_rinn ന്‍റെ പരിരക്ഷിയ്ക്കു വേണ്ടി നിങ്ങൾ തിരഞ്ഞെടുത്ത എസ്ബിഐ ലൈഫ് - ഋണ രക്ഷ പ്ലാനിന്‍റെ പ്രീ-ഇഷ്യുവൻസ് വെരിഫിക്കേഷൻ പ്രക്രിയയിലേക്ക് സ്വാഗതം. നിങ്ങളുടെ ഫോം നമ്പർ സ്ക്രീനിൽ കാണിച്ചിട്ടുണ്ട്. ഞങ്ങളുമായുള്ള എല്ലാ ഭാവി ആശയവിനിമയങ്ങൾക്കും നിങ്ങൾക്ക് ഈ ഫോം നമ്പർ ഉദ്ധരിക്കാം.";

                        case 'tam':
                            $loan_type_rinn = ($loanCategory == "Home Loan") ? "வீட்டுக் கடன்" : "தனிப்பட்ட கடன்";
                            return "எஸ்பிஐ லைஃப்-ஐ உங்களுடைய ஆயுள் காப்பீட்டு பங்காளராக தேர்வு செய்துள்ளமைக்கு மிக்க நன்றி, நீங்கள் தேர்வு செய்த எஸ்பிஐ லைஃப் ரின்ரக்க்ஷா $loan_type_rinn முன்மொழிவின் முன் வழங்கல் சரிபார்ப்பு செயல்முறைக்கு அன்புடன் வரவேற்கிறோம், உங்கள் படிவு என் திரையில் காட்டப்படும். நீங்கள் எதிர்காலத்தில் எங்களுடனான அனைத்து தொடர்புகளிலும் இந்த படிவ எண்ணை குறிப்பிடலாம்.";

                        case 'mar':
                            $loan_type_rinn = ($loanCategory == "Home Loan") ? "होम लोन" : "पर्सनल लोन";
                            return "तुमच्या पसंतीचे लाइफ इन्शुरन्स पार्टनर म्हणून एसबीआय लाइफची निवड केल्याबद्दल धन्यवाद, तुमच्या $loan_type_rinn सुरक्षेसाठी तुम्ही निवडलेल्या तुमच्या एसबीआय लाइफ ऋण रक्षा प्लॅनकरिता असलेल्या प्री-इश्युअन्स व्हेररिफिकेशनमध्ये तुमचे स्वागत, तुमचा फॉर्म स्क्रीनवर डिस्प्ले करण्यात आला आहे, आमच्याबरोबर करावयाच्या सर्व भावी पत्रव्यवहारासाठी तुम्ही हा फॉर्म नंबर नमूद करू शकता.";

                        case 'guj':
                            $loan_type_rinn = ($loanCategory == "Home Loan") ? "હોમ લોન" : "પર્સનલ લોન";
                            return "એસબીઆઈ લાઈફને તમારા પસંદગીના લાઈફ ઈન્શ્યૉરન્સ પાર્ટનર તરીકે પસંદ કરવા બદલ આભાર, તમારી $loan_type_rinn ને સુરક્ષિત કરવા તમે પસંદ કરેલા એસબીઆઈ લાઇફ - ઋણરક્ષા પ્લાન માટેની પ્રી-ઈશ્યુઅન્સ વેરિફિકેશન પ્રક્રિયામાં આપનું સ્વાગત છે, તમારો ફોર્મ નંબર સ્ક્રીન પર દર્શાવ્યો છે. અમારી સાથેના દરેક ભાવિ પત્રવ્યવહારમાં તમે આ ફોર્મ નંબરનો ઉલ્લેખ કરી શકો છો.";

                        case 'ori':
                            $loan_type_rinn = ($loanCategory == "Home Loan") ? "ଗୃହ ଋଣ" : "ବ୍ୟକ୍ତିଗତ ଋଣ";
                            return "ଆପଣଙ୍କର ପ୍ରାଥମିକତା ଦିଆଯାଉଥିବା ଜୀବନ ବୀମା ସହଯୋଗୀ ଭାବରେ ଏସ୍‌ବିଆଇ ଲାଇଫ୍‌କୁ ବାଛିବା ପାଇଁ ଆପଣଙ୍କୁ ଧନ୍ୟବାଦ।. ଆପଣଙ୍କର $loan_type_rinn ର ସୁରକ୍ଷା ପାଇଁ ଆପଣଙ୍କ ଦ୍ୱାରା ଚୟନ କରାଯାଇଥିବା ଏସବିଆଇ ଲାଇଫ୍ - ଋଣ ରକ୍ଷା ପ୍ଲାନ୍ ପାଇଁ ଜାରି ପୂର୍ବବର୍ତ୍ତୀ ଯାଞ୍ଚକରଣ ପ୍ରକ୍ରିୟାକୁ ସ୍ୱାଗତ।, ଆପଣଙ୍କର ଫର୍ମ ନମ୍ବର ସ୍କ୍ରିନ୍‌ରେ ପ୍ରଦର୍ଶିତ ହୋଇଥାଏ। ଆପଣ ଆମ ସହିତ ଭବିଷ୍ୟତରେ ସମସ୍ତ ଯୋଗାଯୋଗ ପାଇଁ ଏହି ଫର୍ମ ନମ୍ବର କୋଟ୍ କରିପାରିବେ।.";

                        case 'pun':
                            $loan_type_rinn = ($loanCategory == "Home Loan") ? "ਹੋਮ ਲੋਨ" : "ਪਰਸਨਲ ਲੋਨ";
                            return "ਐਸਬੀਆਈ ਲਾਈਫ਼ ਨੂੰ ਆਪਣੇ ਪਸੰਦੀਦਾ ਲਾਈਫ਼ ਇੰਸ਼ੈਰੈਂਸ ਪਾਟਨਰ ਦੇ ਰੂਪ ਵਿੱਚ ਚੁਣਨ ਲਈ ਤੁਹਾਡਾ ਧੰਨਵਾਦ ।, ਤੁਹਾਡੇ  $loan_type_rinn ਦੀ ਸੁਰੱਖਿਆ ਲਈ ਤੁਹਾਡੇ ਦੁਆਰਾ ਚੁਣੀ ਗਈ ਐਸਬੀਆਈ ਲਾਈਫ਼ - ਰਿੱਣ ਰੱਕਸ਼ਾ ਯੋਜਨਾ ਲਈ ਜਾਰੀ-ਪੂਰਵ ਤਸਦੀਕ ਪ੍ਰਕ੍ਰਿਆ ਵਿੱਚ ਤੁਹਾਡਾ ਸੁਆਗਤ ਹੈ।, ਤੁਹਾਡਾ ਫਾਰਮ ਨੰ. ਸਕ੍ਰੀਨ ਤੇ ਪ੍ਰਦਸ਼ਿਤ ਕੀਤਾ ਗਿਆ ਹੈ, ਤੁਸੀਂ ਸਾਡੇ ਨਾਲ ਹੋਣ ਵਾਲੇ ਭਵਿੱਖ ਸੰਚਾਰਾਂ ਲਈ ਇਸ ਫਾਰਮ ਨੰ. ਦਾ ਸੰਦਰਭ ਦੇ ਸਕਦੇ ਹੋ.";

                        case 'ben':
                            $loan_type_rinn = ($loanCategory == "Home Loan") ? "হোম লোন" : "পার্সোনাল লোন";
                            return "আপনার পছন্দের লাইফ ইন্স্যুরেন্স পার্টনার হিসাবে এসবিআই লাইফ’কে বেছে নেওয়ার জন্যে ধন্যবাদ।, আপনার  $loan_type_rinn রক্ষা করতে আপনার দ্বারা বেছে নেওয়া আপনার এসবিআই লাইফ - ঋণ রক্ষা প্ল্যানের জন্যে জারি করা-পূর্বক যাচাইকরণ প্রক্রিয়াতে স্বাগতম, আপনার ফর্ম নং স্ক্রীনে প্রদর্শিত হয়েছে। আমাদের সঙ্গে ভবিষ্যতে সকল যোগাযোগের জন্যে এই ফর্ম নং আপনি উদ্ধৃত করতে পারেন.";

                        case 'ass':
                            $loan_type_rinn = ($loanCategory == "Home Loan") ? "হোম লোনৰ" : "পাৰ্চনেল লোনৰ";
                            return "আপোনাৰ পছন্দৰ জীৱন বীমা সঙ্গী ৰূপে এচবিআই লাইফ-ক বাছি লোৱাৰ বাবে অশেষ ধন্যবাদ।, আপোনাৰ $loan_type_rinn সুৰক্ষিত ৰাখিবলৈ আপুনি বাছি লোৱা আপোনাৰ এচবিআই লাইফ – ঋণৰক্ষা প্লেনৰ পলিচী জাৰি-পূৰ্বৰ সত্যনিৰূপণ প্ৰক্ৰিয়ালৈ আপোনাক স্বাগতম, আপোনাৰ ফৰ্ম নং স্ক্ৰীনত প্ৰদৰ্শন কৰা হৈছে। আমাৰ সৈতে ভৱিষ্যতে আপুনি কৰিবলগীয়া সকলো পত্র-যোগাযোগতে এই ফর্ম নংটো উল্লেখ কৰি দিব।.";

                        case 'miz':
                            $loan_type_rinn = ($loanCategory == "Home Loan") ? "Home Loan" : "Personal Loan";
                            return "I life insurance atana SBI Life i thlam avangin Kan lawm e. SBI Life – Rinn Raksha plan I $loan_type_rinn ven himna atana I thlan pek chhuah inih hma a finfiahna hmun ah kan lo lawm ache. I Form No hi screen ah hian tarlan a ni. He Form No. hi nakin zela inbiakpawh a tul hunah I hmang tangkai thei zel ang.";

                        case 'maw':
                            $loan_type_rinn = ($loanCategory == "Home Loan") ? "होम लोन" : "पर्सनल लोन";
                            return "आपरी पसन्‍द रै लाइफ इन्‍श्‍योरेन्‍स पार्टनर रै रूप मैं एसबीअई लाइफ नै चुनण वासते आपरो धन्‍यवाद. थारै $loan_type_rinn री सुरक्षा रै वासतै थारे चुनयोड़े एसबीआई लाइफ - ऋणरक्षा प्लान नै जारी करण स्यूं पैली सत्यापन री प्रक्रिया रै मांय आपरो स्वागत है।, थारो फॉर्म नंबर स्‍क्रीन पर दरसायो गियो है। म्‍हारै सागै भावी हर पत्राचार रै मांय आप इण फॉर्म नंबर नै लिख सको हो।";



                        default:
                            return "Thank you for choosing SBI life as your preferred life insurance partner. Welcome to the pre-issuance verification process of Your SBI Life - $productName plan chosen by you, to protect your $loanCategory. Your Form number is displayed on the screen. You can quote this Form number for all future communications with us.";

                    }

                case 'personal':
                    switch ($language) {
                        case 'hin':
                            return "कृपया स्क्रीन पर दिखाई गई व्यक्तिगत विवरणों को सत्यापित करें। कृपया ध्यान दें कि ये विवरण आपके प्रस्ताव के स्वीकार होने के बाद आपके बीमा प्रमाणपत्र का हिस्सा बनेंगे।";

                        case 'kan':
                            return "ದಯವಿಟ್ಟು ಸ್ಕ್ರೀನ್ ಮೇಲೆ ತೋರಿಸಿರುವ ವೈಯಕ್ತಿಕ ವಿವರಗಳನ್ನು ಪರಿಶೀಲಿಸಿರಿ. ನಿಮ್ಮ ಪ್ರಪೋಸಲ್‌ ಅನ್ನು ಸ್ವೀಕರಿಸಿದ ನಂತರ ಇವು ನಿಮ್ಮ ಪಾಲಿಸಿ ದಾಖಲೆಪತ್ರದ ಭಾಗವಾಗುತ್ತವೆ.";

                        case 'tel':
                            return "దయచేసి స్క్రీన్ పైన ప్రదర్శిస్తున్న వివరాలను ధృవీకరించండి. మీ ప్రతిపాదనను ఆమోదించిన తర్వాత ఇవన్నీ మీ పాలసీ దస్తావేజులో భాగం అవుతాయి.";

                        case 'mal':
                            return "ദയവായി സ്ക്രീനിൽ പ്രദർശിപ്പിച്ചിരിക്കുന്ന വ്യക്തിപരമായ വിവരങ്ങൾ സ്ഥിരീകരിക്കുക. ഈ വിവരങ്ങൾ നിങ്ങളുടെ പ്രൊപ്പോസൽ സ്വീകരിക്കപ്പെട്ടതിനു ശേഷം നിങ്ങളുടെ പോളിസി ഡോക്യുമെന്‍റിന്‍റെ ഭാഗമാകുന്നതാണ്.";

                        case 'tam':
                            return "தயவுசெய்து திரையில் காட்டப்பட்டுள்ள தனிப்பட்ட விவரங்களை சரிபார்த்துக் கொள்ளவும், இவை உங்களுடைய முன்மொழிவு ஏற்றுக்கொள்ளப்பட்ட பிறகு உங்கள் பாலசியின் அங்கமாக அமையும்.";

                        case 'mar':
                            return "स्क्रीनवर डिस्प्ले केलेले तुमचे तपशील कृपया पडताळून पहा. तुमचा प्रस्ताव स्वीकारल्यानंतर हे तपशील तुमच्या पॉलिसी दस्तावेजाचा भाग बनतील.";

                        case 'guj':
                            return "કૃપા કરી સ્ક્રીન પર દર્શાવેલી વ્યક્તિગત વિગતો ચકાસી લો. આ તમારો પ્રપોઝલ સ્વીકાર્ય થયા પછી તમારા પૉલિસી ડૉક્યુમેંટનો હિસ્સો બનશે.";


                        case 'ori':
                            return "ଦୟାକରି ସ୍କ୍ରିନ୍‌ରେ ପ୍ରଦର୍ଶିତ ବ୍ୟକ୍ତିଗତ ବିବରଣୀ ଯାଞ୍ଚ କରନ୍ତୁ। ଆପଣଙ୍କ ପ୍ରସ୍ତାବ ଗ୍ରହଣ କରାଯିବା ପରେ ଏହା ଆପଣଙ୍କ ପଲିସି ଦଲିଲର ଏକ ଅଂଶ ହେବ।";


                        case 'pun':
                            return "ਕਿਰਪਾ ਸਕ੍ਰੀਨ ਤੇ ਪ੍ਰਦਰਸ਼ਿਤ ਵਿਅਕਤੀਗਤ ਵੇਰਵਿਆਂ ਦੀ ਪੁਸ਼ਟੀ ਕਰੋ । ਇਹ ਤੁਹਾਡਾ ਪ੍ਰਸਤਾਵ ਸਵੀਕਾਰ ਕਰਨ ਦੇ ਬਾਅਦ ਤੁਹਾਡੀ ਪਾੱਲਿਸੀ ਦਸਤਾਵੇਜ ਦਾ ਹਿੱਸਾ ਬਣਨਗੇ ।.";

                        case 'ben':
                            return "অনুগ্রহ করে স্ক্রীনে প্রদর্শিত ব্যক্তিগত বিবরণ যাচাই করুন। এইসব আপনার প্রোপোজাল গৃহীত হওয়ার পর আপনার পলিসি নথিপত্রের একটি অংশ হয়ে যাবে।.";


                        case 'ass':
                            return "স্ক্রীনত প্রদর্শন কৰা ব্যক্তিগত বিৱৰণ অনুগ্রহ কৰি সঁচা হয়নে নহয় চাওক। আপোনাৰ প্রস্তাৱ গ্রহণ কৰাৰ পিছত ই আপোনাৰ পলিচী ডক্যুমেন্টৰ অংশ হৈ পৰিব।";


                        case 'miz':
                            return "Khawngaih takin i mimal chanchin screen a tihlan hi a dik leh dik loh i ti chiang dawn nia. Heng hi nakina i dilna pawm anih hnu a i lehkha pawimawh tur te an ni.";

                        case 'maw':
                            return "स्‍क्रीन पर दरसायोड़ी व्‍यक्तिगत जाणकारी नै सत्‍यापित करण री किरपा करो. आपरै प्रोपोजल री स्‍वीकृति पछै ऐ थारे पॉलिसी डॉकूमेन्‍ट रा हिस्‍सा बण जासी.";

                        default:
                            return "Please verify the personal details displayed on the screen. Please note these details will form part of your Certificate of Insurance after your proposal is accepted.";
                    }

                    case 'personal_disagree':
                        switch ($language) {
                            case 'hin':
                                return "कृपया नीले रंग से चिह्नित बक्सों में आवश्यक सुधार करें और फिर आगे बढ़ें पर टैप करें.";
    
                            case 'kan':
                                return "ಒದಗಿಸಿದ ಬಾಕ್ಸ್‌ನಲ್ಲಿ ನಿಮ್ಮ ಭಿನ್ನಾಭಿಪ್ರಾಯವನ್ನು ನಮೂದಿಸಿ ಮತ್ತು ಮುಂದುವರೆಯಲು ಟ್ಯಾಪ್ ಮಾಡಿ.";
    
                            case 'tel':
                                return "పైనగల బాక్సులో మీరు అంగీకరించకపోవడాన్ని నమోదు చేసి ’సేవ్ అండ్ ప్రొసీడ్’ని క్లిక్ చెయ్యండి.";
    
                            case 'mal':
                                return "നൽകിയിരിക്കുന്ന ബോക്സിൽ നിങ്ങളുടെ വിയോജിപ്പ് രേഖപ്പെടുത്തി മുന്നോട്ട് ടാപ്പുചെയ്യുക";
    
                            case 'tam':
                                return "கொடுக்கப்பட்டுள்ள பெட்டியில் உங்கள் கருத்து வேறுபாட்டை உள்ளிட்டு, தொடரவும் என்பதைத் தட்டவும்.";
    
                            case 'mar':
                                return "प्रदान केलेल्या बॉक्समध्ये तुमचे असहमत प्रविष्ट करा आणि पुढे जा वर टॅप करा.";
    
                            case 'guj':
                                return "આપેલા બૉક્સમાં તમારો મતભેદ દાખલ કરો અને આગળ વધો પર ટૅપ કરો.";
    
    
                            case 'ori':
                                return "ଉପରେ ପ୍ରଦାନ କରାଯାଇଥିବା ବକ୍ସରେ ଆପଣଙ୍କ ଅସହମତି ଏଣ୍ଟର କରନ୍ତୁ ଏବଂ ‘ସେଭ୍ ଓ ପ୍ରୋସିଡ୍’ କ୍ଲିକ୍ କରନ୍ତୁ";
    
    
                            case 'pun':
                                return "ਪ੍ਰਦਾਨ ਕੀਤੇ ਗਏ ਬਾਕਸ ਵਿੱਚ ਆਪਣੀ ਅਸਹਿਮਤੀ ਦਰਜ ਕਰੋ ਅਤੇ ਅੱਗੇ ਵਧੋ 'ਤੇ ਟੈਪ ਕਰੋ";
    
                            case 'ben':
                                return "প্রদত্ত বাক্সে আপনার মতবিরোধ লিখুন এবং এগিয়ে যান আলতো চাপুন";
    
    
                            case 'ass':
                                return "প্ৰদত্ত বাকচটোত আপোনাৰ অসন্মতি প্ৰবিষ্ট কৰক আৰু আগবাঢ়ক টেপ কৰক";
    
    
                            case 'miz':
                                return "I inrem lohna chu box pek tawhah ziak la, proceed tih kha tap rawh";
    
                            case 'maw':
                                return "दिए गए बॉक्स में अपनी असहमति दर्ज करें और आगे बढ़ें पर टैप करें.";
    
                            default:
                                return "Please verify the personal details displayed on the screen. Please note these details will form part of your Certificate of Insurance after your proposal is accepted.";
                        }


                case 'medical':
                    switch ($language) {
                        case 'hin':
                            return  "हम चाहते हैं कि आप इस बात की पुष्टि करें कि आपने प्रपोज़ल में दिए गए सभी चिकित्सा सम्बंधी प्रश्नों को सही ढंग से पढ़ा और उनका उत्तर दिया है और चिकित्सा/ उपचार के इतिहास (यदि कोई हो) से सम्बंधित सभी विवरणों का खुलासा किया है। [किसी भी प्रतिकूल चिकित्सा इतिहास के गैर-प्रकटीकरण से भविष्य में क्लेम की अस्वीकृति हो सकती है]";

                        case 'kan':
                            return "ನೀವು ಪ್ರಸ್ತಾವದಲ್ಲಿನ ಎಲ್ಲಾ ವೈದ್ಯಕೀಯ ಪ್ರಶ್ನೆಗಳನ್ನು , ಓದಿದ್ದೀರಿ ಮತ್ತು ಸರಿಯಾಗಿ  ಉತ್ತರಿಸಿದ್ದೀರಿ ಎಂದು ಮತ್ತು ,  ವೈದ್ಯಕೀಯ/ಚಿಕಿತ್ಸೆಯ ಹಿನ್ನೆಲೆಯ (ಏನಾದರೂ ಇದ್ದರೆ) ಎಲ್ಲಾ ವಿವರಗಳನ್ನೂ , ಬಹಿರಂಗಪಡಿಸಿದ್ದೀರಿ ಎಂದು ನೀವು ದೃಢೀಕರಿಸಬೇಕೆಂದು ನಾವು ಇಚ್ಚಿಸುತ್ತೇವೆ , ಯಾವುದೇ ಪ್ರತಿಕೂಲ ವೈದ್ಯಕೀಯ ಹಿನ್ನೆಲೆಯನ್ನು ಬಹಿರಂಗಪಡಿಸದಿರುವುದು ,  ಭವಿಷ್ಯದಲ್ಲಿ ನಿಮ್ಮ ಕ್ಲೈಮನ್ನು ತಿರಸ್ಕರಿಸುವುದಕ್ಕೆ ಕಾರಣವಾಗಬಹುದು";

                        case 'tel':
                            return "ప్రొపోసల్లోని అన్ని వైద్య ప్రశ్నలను మీరు సరిగ్గా చదివి మరియు సమాధానాలు అందించారని మరియు అన్ని వైద్య వివరాలను/చికిత్స చరిత్రను (ఏదైనా ఉంటే) వెల్లడించారని ధృవీకరించవలసిందిగా మేము కోరుతున్నాము. [ఏదైనా ప్రతికూల వైద్య చరిత్ర ఉండి వెల్లడించని పక్షంలో భవిష్యత్తులో క్లెయిం తిరస్కరించడం జరగవచ్చు].";

                        case 'mal':
                            return "പ്രൊപ്പോസലിലെ എല്ലാ വൈദ്യസംബന്ധമായ ചോദ്യങ്ങളും നിങ്ങൾ വായിക്കുകയും അവയ്ക്ക് ശരിയായ ഉത്തരം നൽകുകയും ചെയ്തു എന്നും എല്ലാ മെഡിക്കൽ/ചികിത്സാ ചരിത്രവും (ഉണ്ടെങ്കിൽ) വെളിപ്പെടുത്തി എന്നും നിങ്ങൾ സ്ഥിരീകരിക്കണമെന്നാണ് ഞങ്ങളുടെ ആഗ്രഹം. [പ്രതികൂലമായ മെഡിക്കൽ ചരിത്രം വെളിപ്പെടുത്താതിരിക്കുന്നത് ഭാവിയിൽ ക്ലെയിം നിരസിക്കപ്പെടാൻ ഇടയാക്കും]";

                        case 'tam':
                            return "ப்ரொபோசலில் உள்ள அனைத்து மருத்துவ கேள்விகளையும் நீங்கள் படித்து புரிந்துகொண்டு விடை அளித்திருப்பதாக மற்றும் அனைத்து மருத்துவ / சிகிச்சை (ஏதேனும் இருப்பின்) சரித்திர விவரங்களையும் வெளிப்படுத்தி இருப்பதை நீங்கள் உறுதிப்படுத்த வேண்டும் என்று நாங்கள் விரும்புகிறோம் [எந்த பாதகமான மருத்துவ சரித்திரத்தையும் வெளிப்படுத்தாமை எதிர்காலத்தில் கோருரிமை நிராகரிப்பதற்கு வழி வகுக்கலாம்].";

                        case 'mar':
                            return "आम्हाला ह्याची खात्री करून घ्यायला आवडेल की प्रस्तावातील सर्व मेडिकल प्रश्न तुम्ही वाचले आहेत आणि त्याची उत्तरे अचूक दिली आहेत आणि मेडिकल/ उपचारांची पूर्वापार (जी काही असेल ती) माहिती उघड केली आहे. (कोणतीही विपरीत मेडिकल पूर्व-माहिती उघड न केल्यास भावी काळात त्याची परिणती दावा नाकारण्यात होऊ शकते)";

                        case 'guj':
                            return "અમે ઈચ્છીએ છીએ કે તમે પ્રપોઝલનાં તમામ તબીબી પ્રશ્નો વાંચ્યા અને તેનાં સાચાં જવાબો આપ્યાં છે તથા તબીબી/સારવાર ઈતિહાસ (જો કોઈ હોય તો) તેની તમામ વિગતો જાહેર કરી છે તેની પુષ્ટિ કરો. [કોઈ પણ વિપરીત તબીબી ઈતિહાસ જાહેર ન કરવા પર ભવિષ્યમાં કલેઈમ (દાવો) રદ થઈ શકે છે]";


                        case 'ori':
                            return "ଆମେ ଆପଣଙ୍କୁ ସୁନିଶ୍ଚିତ କରିବାକୁ ଚାହୁଁ ଯେ ଆପଣ ଏହି ପ୍ରସ୍ତାବରେ ଥିବା ସମସ୍ତ ଚିକିତ୍ସା ସମ୍ବନ୍ଧିତ ପ୍ରଶ୍ନ ପଢିଛନ୍ତି ଓ ଉତ୍ତର ପ୍ରଦାନ କରିଛନ୍ତି ଏବଂ ମେଡିକଲ୍/ଚିକିତ୍ସା ଇତିହାସ (ଯଦି କୌଣସି ଥାଏ) ର ସମସ୍ତ ବିବରଣୀ ପ୍ରକାଶ କରିଛନ୍ତି । [କୌଣସି ପ୍ରତିକୂଳ ଚିକିତ୍ସା ସମ୍ବନ୍ଧିତ ଇତିହାସର ଅପରିପ୍ରକାଶ ଭବିଷ୍ୟତରେ ଦାବି ରଦ୍ଦର କାରଣ ହୋଇପାରେ]।.";


                        case 'pun':
                            return "ਅਸੀਂ ਚਾਹਾਂਗੇ ਕਿ ਤੁਸੀਂ ਪੁਸ਼ਟੀ ਕਰੋ ਕਿ ਤੁਸੀਂ ਪ੍ਰਸਤਾਵ ਵਿੱਚ ਦਿੱਤੇ ਸਾਰੇ ਮੈਡੀਕਲ ਸਵਾਲ ਪੜ੍ਹ ਲਏ ਹਨ ਅਤੇ ਉਹਨਾਂ ਦੇ ਸਹੀ ਢੰਗ ਨਾਲ਼ ਜਵਾਬ ਦਿੱਤੇ ਹਨ ਅਤੇ ਡਾਕਟਰੀ/ਇਲਾਜ ਦੇ ਪਿਛੋਕੜ ਬਾਰੇ ਸਾਰੀ ਜਾਣਕਾਰੀ ਦੱਸ ਦਿਤੀ ਹੈ (ਜੇ ਕੋਈ ਸੀ)[ਕਿਸੇ ਵੀ ਉਲਟ ਡਾਕਟਰੀ ਜਾਣਕਾਰੀ ਬਾਰੇ ਨਾ ਦੱਸਣ ਕਾਰਨ ਭਵਿੱਖ ਵਿੱਚ ਕ੍ਲੈਮ ਰੱਦ ਹੋ ਸਕਦਾ ਹੈ।]";

                        case 'ben':
                            return "আশা করি আপনি সঠিকভাবে আপনার প্রোপোজাল –এর সকল মেডিকেল প্রশ্নাবলী পড়েছেন ও উত্তর দিয়েছেন আর মেডিকেল/চিকিৎসার ইতিহাসের (যদি কোনও থাকে) সকল বিবরণ প্রকাশ করেছেন৷. (যেকোন বিরূপ মেডিকেল ইতিহাস প্রকাশ না করার দরুন ভবিষ্যতে আপনার ক্লেম বাতিল হতে পারে)|";


                        case 'ass':
                            return "আমি আপোনাক জনাব বিচাৰু যে আপুনি প্ৰস্তাবত থ্কা চিকিৎ্সা প্ৰস্ন সমূহ পঢ়িছে আৰু সথিক ভাবে উওৰ দিছে আৰু পূৰ্বৰ সকলো চিকিৎ্সা তথ্য় (যদি আছে )উল্লেখ কৰিছে [যিকোনো প্রতিকূল চিকিত্সা ইতিহাস ব্যক্ত নকৰাৰ পৰিণাম স্বৰুপে ভৱিষ্যতে দাবী নামঞ্জুৰ কৰা যাব পাৰে].";


                        case 'miz':
                            return "Medical chungchanga kan zawhna te Proposal ah hian i chhang kim tawh a, I medical detail leh treatment chungchang pawh I rawn hriatti rtawh a ni.[I medical chungchang ah thu hriattir famkim lo/diklo a awm anih chuan hun lo kaltur ah I claim hnawl a ni thei.].";

                        case 'maw':
                            return "थे ईं बात री पुष्टि करो कै थे प्रस्ताव रै सगळा चिकित्‍सा प्रश्‍नां नै पढ़ लियो है ऐर सही-सही जवाब दे दियो है ऐर चिकित्‍सा ब्यौरा/उपचार रै इतिहास बारै (जे कोई हुवै तो) सारी बातां विस्‍तार स्‍यूं बताय दी है. (थानैं पहली कोई बीमारी ओर रही हुवै ऐर थे बीं रै बारै मैं ना बतायो हुवै तो थारो क्‍लेम रिजेक्‍ट हो सकै है)";

                        default:
                            return "We would like you to confirm that you have read and answered all the medical questions in the proposal correctly and disclosed all details of medical/treatment history (if any). [Non-disclosure of any adverse medical history may lead to rejection of claim in future]";
                    }

                    case 'medical_disagree':
                        switch ($language) {
                            case 'hin':
                                return  "दिए गए बॉक्स में अपनी असहमति दर्ज करें और आगे बढ़ें पर टैप करें.";
    
                            case 'kan':
                                return "ಒದಗಿಸಿದ ಬಾಕ್ಸ್‌ನಲ್ಲಿ ನಿಮ್ಮ ಭಿನ್ನಾಭಿಪ್ರಾಯವನ್ನು ನಮೂದಿಸಿ ಮತ್ತು ಮುಂದುವರೆಯಲು ಟ್ಯಾಪ್ ಮಾಡಿ.";
    
                            case 'tel':
                                return "పైనగల బాక్సులో మీరు అంగీకరించకపోవడాన్ని నమోదు చేసి ’సేవ్ అండ్ ప్రొసీడ్’ని క్లిక్ చెయ్యండి.";
    
                            case 'mal':
                                return "നൽകിയിരിക്കുന്ന ബോക്സിൽ നിങ്ങളുടെ വിയോജിപ്പ് രേഖപ്പെടുത്തി മുന്നോട്ട് ടാപ്പുചെയ്യുക";
    
                            case 'tam':
                                return "கொடுக்கப்பட்டுள்ள பெட்டியில் உங்கள் கருத்து வேறுபாட்டை உள்ளிட்டு, தொடரவும் என்பதைத் தட்டவும்.";
    
                            case 'mar':
                                return "प्रदान केलेल्या बॉक्समध्ये तुमचे असहमत प्रविष्ट करा आणि पुढे जा वर टॅप करा.";
    
                            case 'guj':
                                return "આપેલા બૉક્સમાં તમારો મતભેદ દાખલ કરો અને આગળ વધો પર ટૅપ કરો";
    
    
                            case 'ori':
                                return "ଉପରେ ପ୍ରଦାନ କରାଯାଇଥିବା ବକ୍ସରେ ଆପଣଙ୍କ ଅସହମତି ଏଣ୍ଟର କରନ୍ତୁ ଏବଂ ‘ସେଭ୍ ଓ ପ୍ରୋସିଡ୍’ କ୍ଲିକ୍ କରନ୍ତୁ";
    
    
                            case 'pun':
                                return "ਪ੍ਰਦਾਨ ਕੀਤੇ ਗਏ ਬਾਕਸ ਵਿੱਚ ਆਪਣੀ ਅਸਹਿਮਤੀ ਦਰਜ ਕਰੋ ਅਤੇ ਅੱਗੇ ਵਧੋ 'ਤੇ ਟੈਪ ਕਰੋ";
    
                            case 'ben':
                                return "প্রদত্ত বাক্সে আপনার মতবিরোধ লিখুন এবং এগিয়ে যান আলতো চাপুন";
    
    
                            case 'ass':
                                return "প্ৰদত্ত বাকচটোত আপোনাৰ অসন্মতি প্ৰবিষ্ট কৰক আৰু আগবাঢ়ক টেপ কৰক";
    
    
                            case 'miz':
                                return "I inrem lohna chu box pek tawhah ziak la, proceed tih kha tap rawh";
    
                            case 'maw':
                                return "दिए गए बॉक्स में अपनी असहमति दर्ज करें और आगे बढ़ें पर टैप करें.";
    
                            default:
                                return "Enter your disagreement in the box provided and tap on proceed.";
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

                        case 'kan':
                            $formattedText = [];
                            foreach ($dataListValue['response']['input'] as $key => $value) {
                                if (!empty($value)) {
                                    switch ($key) {
                                        case "medicalConditionPresent":
                                            $formattedText[] = "1.ಯಾವುದಾದರೂ ಕಾಯಿಲೆಗಾಗಿ ಈಗ ಚಿಕಿತ್ಸೆ ಮಾಡಿಸಿಕೊಳ್ಳುತ್ತಿದ್ದೀರಾ?
                                            <br>$value</br>";
                                            break;
                                        case "medicalConditionPresent_des":
                                            if (strtolower($dataListValue['response']['input']['medicalConditionPresent']) == 'yes') {
                                                $formattedText[] = "$value";
                                            }
                                            break;
                                        case "treatmentLast_5years":
                                            $formattedText[] = "2.ಕಳೆದ 5 ವರ್ಷಗಳಲ್ಲಿ ನೀವು ಎಂದಾದರೂ ಆಸ್ಪತ್ರೆ ಸೇರಿಕೊಂಡಿದ್ದಿರಾ ಅಥವಾ ಆಪರೇಶನ್ ಮಾಡಿಸಿಕೊಂಡಿದ್ದಿರಾ ಅಥವಾ ಯಾವುದಾದರೂ ಕಾಯಿಲೆಗಾಗಿ ಚಿಕಿತ್ಸೆ ಮಾಡಿಸಿಕೊಂಡಿದ್ದಿರಾ?<br>$value</br>";
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

                        case 'tel':
                            $formattedText = [];
                            foreach ($dataListValue['response']['input'] as $key => $value) {
                                if (!empty($value)) {
                                    switch ($key) {
                                        case "medicalConditionPresent":
                                            $formattedText[] = "1.ప్రస్తుతం మీరు ఏదైనా వైద్య పరీక్ష చికిత్స పొందుతున్నారా?<br>$value</br>";
                                            break;
                                        case "medicalConditionPresent_des":
                                            if (strtolower($dataListValue['response']['input']['medicalConditionPresent']) == 'yes') {
                                                $formattedText[] = "$value";
                                            }
                                            break;
                                        case "treatmentLast_5years":
                                            $formattedText[] = "2.మీరు గత 5 సంవత్సరాలలో ఏదైనా వ్యాధికి సంబంధించి ఆస్పత్రిలో చేరారా లేదా శస్త్రచికిత్స చేసుకున్నారా లేదా చికిత్స తీసుకున్నారా ?<br>$value</br>";
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
    
                        case 'mal':
                        $formattedText = [];
                        foreach ($dataListValue['response']['input'] as $key => $value) {
                            if (!empty($value)) {
                                switch ($key) {
                                    case "medicalConditionPresent":
                                        $formattedText[] = "1.നിങ്ങൾ നിലവിൽ ഏതെങ്കിലും രോഗാവസ്ഥയ്ക്ക് ചികിത്സയിലാണോ?<br>$value</br>";
                                        break;
                                    case "medicalConditionPresent_des":
                                        if (strtolower($dataListValue['response']['input']['medicalConditionPresent']) == 'yes') {
                                            $formattedText[] = "$value";
                                        }
                                        break;
                                    case "treatmentLast_5years":
                                        $formattedText[] = "2.കഴിഞ്ഞ 5 വർഷങ്ങൾക്കിടയിൽ ഏതെങ്കിലും അസുഖത്തിനു വേണ്ടി നിങ്ങൾ ആശുപ്രതിയിൽ കിടക്കുകയോ ഓപ്പറേഷൻ ചെയ്യുകയോ ചികിത്സയ്ക്കു വിധേയമാകുകയോ ചെയ്തിട്ടുണ്ടോ?<br>$value</br>";
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
    
                        case 'tam':
                        $formattedText = [];
                        foreach ($dataListValue['response']['input'] as $key => $value) {
                            if (!empty($value)) {
                                switch ($key) {
                                    case "medicalConditionPresent":
                                        $formattedText[] = "1.நீங்கள் தற்பொழுது எந்த மருத்துவ நிலைக்காகவும் சிகிச்சை மேற்கொண்டிருக்கிறீர்களா?<br>$value</br>";
                                        break;
                                    case "medicalConditionPresent_des":
                                        if (strtolower($dataListValue['response']['input']['medicalConditionPresent']) == 'yes') {
                                            $formattedText[] = "$value";
                                        }
                                        break;
                                    case "treatmentLast_5years":
                                        $formattedText[] = "2.கடந்த 5 ஆண்டுகளில் நீங்கள் எந்த நோய்க்காகவும் மருத்துவமனையில் சேர்க்கப்பட்டீர்களா அல்லது அறுவைசிகிச்சை அல்லது சிகிச்சை செய்யப்பட்டதா?<br>$value</br>";
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
    
                        case 'mar':
                        $formattedText = [];
                        foreach ($dataListValue['response']['input'] as $key => $value) {
                            if (!empty($value)) {
                                switch ($key) {
                                    case "medicalConditionPresent":
                                        $formattedText[] = "1.तुम्ही सध्या कोणत्याही मेडिकल स्थितीसाठी औषधोपचार घेत आहात का?<br>$value</br>";
                                        break;
                                    case "medicalConditionPresent_des":
                                        if (strtolower($dataListValue['response']['input']['medicalConditionPresent']) == 'yes') {
                                            $formattedText[] = "$value";
                                        }
                                        break;
                                    case "treatmentLast_5years":
                                        $formattedText[] = "2.गेल्या 5 वर्षांमध्ये तुम्हाला कोणत्याही व्याधीसाठी हॉस्पिटामध्ये दाखल व्हावे लागले आहे का किंवा शस्त्रक्रिया झाली आहे का किंवा काही औषधोपचार घ्यावे लागले आहेत का?<br>$value</br>";
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
    
                        case 'guj':
                                $formattedText = [];
                                foreach ($dataListValue['response']['input'] as $key => $value) {
                                    if (!empty($value)) {
                                        switch ($key) {
                                            case "medicalConditionPresent":
                                                $formattedText[] = "1.શું તમે હાલ કોઈ તબીબી સ્થિતિ માટે સારવાર મેળવી રહ્યાં છો?<br>$value</br>";
                                                break;
                                            case "medicalConditionPresent_des":
                                                if (strtolower($dataListValue['response']['input']['medicalConditionPresent']) == 'yes') {
                                                    $formattedText[] = "$value";
                                                }
                                                break;
                                            case "treatmentLast_5years":
                                                $formattedText[] = "2.શું છેલ્લા 5 વર્ષમાં તમે કોઈ બીમારી માટે હૉસ્પિટલમાં દાખલ થયા છો અથવા શસ્ત્રક્રિયા કરાવી છે કે પછી કોઈ સારવાર મેળવી છે?<br>$value</br>";
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
    
                        case 'ori':
                        $formattedText = [];
                        foreach ($dataListValue['response']['input'] as $key => $value) {
                            if (!empty($value)) {
                                switch ($key) {
                                    case "medicalConditionPresent":
                                        $formattedText[] = "1.ଆପଣ ବର୍ତ୍ତମାନ କୌଣସି ସ୍ୱାସ୍ଥ୍ୟଗତ ସମସ୍ୟା ପାଇଁ ଚିକିତ୍ସିତ ହେଉଛନ୍ତି କି?<br>$value</br>";
                                        break;
                                    case "medicalConditionPresent_des":
                                        if (strtolower($dataListValue['response']['input']['medicalConditionPresent']) == 'yes') {
                                            $formattedText[] = "$value";
                                        }
                                        break;
                                    case "treatmentLast_5years":
                                        $formattedText[] = "2.ଆପଣ ଗତ 5 ବର୍ଷ ମଧ୍ୟରେ କୌଣସି ଅସୁବିଧା ପାଇଁ ଡାକ୍ତରଖାନାରେ ଦାଖଲ ହୋଇଛନ୍ତି କିମ୍ବା ଅସ୍ତ୍ରୋପଚାର କରାଯାଇଛି କିମ୍ବା ଚିକିତ୍ସିତ ହୋଇଛନ୍ତି କି?<br>$value</br>";
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
    
                        case 'pun':
                            $formattedText = [];
                            foreach ($dataListValue['response']['input'] as $key => $value) {
                                if (!empty($value)) {
                                    switch ($key) {
                                        case "medicalConditionPresent":
                                            $formattedText[] = "1.ਕੀ ਵਰਤਮਾਣ ਵਿੱਚ ਤੁਸੀਂ ਕਿਸੇ ਮੇਡਿਕਲ ਸਥਿਤੀ ਲਈ ਇਲਾਜ਼ ਲੈ ਰਹੇ ਹੋ ?<br>$value</br>";
                                            break;
                                        case "medicalConditionPresent_des":
                                            if (strtolower($dataListValue['response']['input']['medicalConditionPresent']) == 'yes') {
                                                $formattedText[] = "$value";
                                            }
                                            break;
                                        case "treatmentLast_5years":
                                            $formattedText[] = "2. ਕੀ ਪਿੱਛਲੇ 5 ਸਾਲਾਂ ਵਿੱਚ ਤੁਹਾਨੂੰ ਕਦੇ ਹਸਪਤਾਲ ਵਿੱਚ ਭਰਤੀ ਜਾਂ ਕਿਸੇ ਬਿਮਾਰੀ ਲਈ ਆੱਪਰੇਟ ਜਾਂ ਇਲਾਜ਼ ਕੀਤਾ ਗਿਆ ਹੈ?<br>$value</br>";
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
    
                        case 'ben':
                        $formattedText = [];
                        foreach ($dataListValue['response']['input'] as $key => $value) {
                            if (!empty($value)) {
                                switch ($key) {
                                    case "medicalConditionPresent":
                                        $formattedText[] = "1.বর্তমানে আপনি কী কোনও মেডিক্যাল অবস্থার জন্যে চিকিৎসাধীনে আছেন?<br>$value</br>";
                                        break;
                                    case "medicalConditionPresent_des":
                                        if (strtolower($dataListValue['response']['input']['medicalConditionPresent']) == 'yes') {
                                            $formattedText[] = "$value";
                                        }
                                        break;
                                    case "treatmentLast_5years":
                                        $formattedText[] = "2.আপনি কী বিগত 5 বছরে কোনও অসুস্থতার জন্যে হাসপাতালে ভির্ত্ত বা অপারেশন করানো বা চিকিৎসার অধীন হয়েছিলেন?<br>$value</br>";
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
    
                        case 'ass':
                        $formattedText = [];
                        foreach ($dataListValue['response']['input'] as $key => $value) {
                            if (!empty($value)) {
                                switch ($key) {
                                    case "medicalConditionPresent":
                                        $formattedText[] = "1.বর্তমান আপুনি কোনো ৰুগ্ন অৱস্থাৰ বাবে চিকিৎসা গ্ৰহণ কৰি আছেনেকি?<br>$value</br>";
                                        break;
                                    case "medicalConditionPresent_des":
                                        if (strtolower($dataListValue['response']['input']['medicalConditionPresent']) == 'yes') {
                                            $formattedText[] = "$value";
                                        }
                                        break;
                                    case "treatmentLast_5years":
                                        $formattedText[] = "2.বিগত 5 বছৰত আপুনি চিকিৎসালয়ত ভর্তি বা আপোনাৰ কোনো অপাৰেশ্যন বা কোনো অসুস্থতাৰ আপুনি চিকিৎসাধীন হৈ আছিলনেকি?<br>$value</br>";
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
    
                        case 'miz':
                        $formattedText = [];
                        foreach ($dataListValue['response']['input'] as $key => $value) {
                            if (!empty($value)) {
                                switch ($key) {
                                    case "medicalConditionPresent":
                                        $formattedText[] = "1.Hriselna lamah damdawi inenkawlna lak lai mek I nei em?<br>$value</br>";
                                        break;
                                    case "medicalConditionPresent_des":
                                        if (strtolower($dataListValue['response']['input']['medicalConditionPresent']) == 'yes') {
                                            $formattedText[] = "$value";
                                        }
                                        break;
                                    case "treatmentLast_5years":
                                        $formattedText[] = "2.Kum 5 kalta ah khan damdawi inah I awm emaw, zai I tawk emaw, damdawi hmanga inenkawlna lak I nei em?<br>$value</br>";
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
    
                        case 'maw':
                            $formattedText = [];
                            foreach ($dataListValue['response']['input'] as $key => $value) {
                                if (!empty($value)) {
                                    switch ($key) {
                                        case "medicalConditionPresent":
                                            $formattedText[] = "1. वर्तमान मैं थारी किण ही बीमारी रो इलाज चाल रियो है के?<br>$value</br>";
                                            break;
                                        case "medicalConditionPresent_des":
                                            if (strtolower($dataListValue['response']['input']['medicalConditionPresent']) == 'yes') {
                                                $formattedText[] = "$value";
                                            }
                                            break;
                                        case "treatmentLast_5years":
                                            $formattedText[] = "2. बीता 5 बरसां रै मांय थे कदी अस्‍पताल मैं भर्ती होया या थारो ऑपरेशन हुयो हुवै या किण ही बीमारी खातर इलाज चाल्‍यो हुवै?<br>$value</br>";
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
                                    $questionText = "3.क्या आप मेडिकल प्रश्नावली के लिए प्रस्ताव फॉर्म में दी गई प्रतिक्रियाओं की समीक्षा करना चाहते हैं?";
                                    break;
        
                                case 'kan':
                                    $questionText = "3.ವೈದ್ಯಕೀಯ ಪ್ರಶ್ನಾವಳಿಗಾಗಿ ಪ್ರಪೋಸಲ್ ಫಾರ್ಮ್ನಲ್ಲಿ ನೀವು ಕೊಟ್ಟಿರುವ ಉತ್ತರಗಳನ್ನು ಪುನಃ ಪರಿಶೀಲಿಸಲು ಇಷ್ಟಪಡುತ್ತೀರಾ?";
                                    break;
        
                                case 'tel':
                                    $questionText = "3.వైద్యకీయ ప్రశ్నావళి కోసం ప్రతిపాదన ఫారంలో అందించిన స్పందనలను పరిశీలించాలని అనుకుంటున్నారా?";
                                    break;
        
                                case 'mal':
                                    $questionText = "3.മെഡിക്കൽ ചോദ്യാവലിയുടെ കാര്യത്തിൽ പ്രൊപ്പോസൽ ഫോമിൽ നിങ്ങൾ നൽകിയിരിക്കുന്ന പ്രതികരണങ്ങൾ പുനരവലോകനം ചെയ്യാൻ നിങ്ങൾ ആഗ്രഹിക്കുന്നുണ്ടോ?";
                                    break;
        
                                case 'tamil':
                                    $questionText = "3.முன்மொழிவு படிவத்தில் கொடுக்கப்பட்ட பதில்களை மருத்துவ வினாத்தாளுக்காக மதிப்பாய்வு செய்ய விரும்புகிறீர்களா?";
                                    break;
        
                                case 'mar':
                                    $questionText = "3.तुम्हाला मेडिकल प्रश्नावलीसाठीच्या प्रपोझल फॉर्म मध्ये तुम्ही दिलेल्या प्रतिसादांचे तुम्हाला पुनरावलोकन करावयाचे आहे का?";
                                    break;
        
                                case 'guj':
                                    $questionText = "3.તબીબી પ્રશ્નાવલી માટેના પ્રસ્તાવ પત્રકમાં તમે આપેલા જવાબોની તમે સમીક્ષા કરવા ચાહો છો?";
                                    break;
        
        
                                case 'ori':
                                    $questionText = "3.ଆପଣ ଡାକ୍ତରୀ ପ୍ରଶ୍ନାବଳି ପାଇଁ ପ୍ରସ୍ତାବ ଫର୍ମରେ ଦିଆଯାଇଥିବା ଆପଣଙ୍କର ଉତ୍ତର ସମୀକ୍ଷା କରିବାକୁ ଚାହାନ୍ତି କି?";
                                    break;
        
        
                                case 'pun':
                                    $questionText = "3.ਕੀ ਤੁਸੀਂ ਮੇਡਿਕਲ ਪ੍ਰਸ਼ਨਾਵਲੀ ਲਈ ਪ੍ਰਸਤਾਵ ਫਾਰਮ ਵਿੱਚ ਦਿੱਤੀ ਗਈ ਪ੍ਰਕ੍ਰਿਆਵਾਂ ਦੀ ਸਮਿੱਖਿਆ ਕਰਵਾਉਣਾ ਚਾਹੁੰਦੇ ਹੋ ?";
                                    break;
        
                                case 'ben':
                                    $questionText = "3.আপনি কী মেডিক্যাল প্রশ্নোত্তরের জন্যে প্রস্তাব ফর্মে দেওয় আপনার প্রতিক্রিয়া, পুনর্সমীক্ষা করতে ইচ্ছুক?";
                                    break;
        
        
                                case 'ass':
                                    $questionText = "3.চিকিৎসা বিষয়ক প্ৰশ্নাৱলীৰ হেতু প্ৰস্তাৱ প্ৰপত্ৰত দিয়া আপোনাৰ উত্তৰ সমূহৰ আপুনি পুনৰুক্ষণ কৰিব বিচাৰেনেকি?";
                                    break;
        
                                case 'miz':
                                    $questionText = "3.Damdawi lampang zawhna Proposal form ami I chhanna hi ennawn leh I duh em?";
                                    break;
        
                                case 'maw':
                                    $questionText = "3.क्या आप मेडिकल प्रश्नावली के लिए प्रस्ताव फॉर्म में दी गई प्रतिक्रियाओं की समीक्षा करना चाहते हैं?";
                                    break;

                               

                                default:
                                    $questionText = "3.Do you wish to review your responses given in the proposal form for medical Questionarie?";
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
