<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Str;
use App\Services\JobService;
use Illuminate\Http\Request;
use App\Services\LinkService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;


class AudioController extends Controller
{
    protected $linkService;
    protected $jobService;
    public function __construct(JobService $jobService,LinkService $linkService)
    {
        $this->linkService = $linkService;
        $this->jobService = $jobService;
    }
    public function playAudioFromPDF($encryptedProposalNo, $screen)
    {
                // $proposalNo = Crypt::decryptString($encryptedProposalNo);
        $proposalNo = $encryptedProposalNo;
        $basePath =  "/var/www/html/sbil_piwc/";
        $links = $this->linkService->checkProposalNoExistDetailspdf($proposalNo);
        if (!$links) {
            return response()->json(['error' => 'Proposal not found'], 404);
        }
        $completed_year = date('Y', strtotime($links['completed_on']));
        $completed_year_minusOne = $completed_year - 1;
        $data_list = $this->jobService->formatPDFCollectedData($links,json_decode($links['reg_photo_url'],TRUE),json_decode($links['consent_image_url'],TRUE),json_decode($links['response'],TRUE));

        $lang = $data_list['Welcome Screen']['image']['language'] ?? 'eng';


        $parameters = json_decode($links['params']);
        $flow_key = $parameters->flow_key ?? null;
        $flow_data = $parameters->flow_data ?? null;

        // Handle guaranteed frequency for Smart Platina Plus
        $smart_platina_keys = [
            'sbilm_smart_platina_plus', 'sbilsa_smart_platina_plus',
            'sbilo_smart_platina_plus', 'sbily_smart_platina_plus',
            'sbilpl_smart_platina_plus'
        ];

        if (in_array($flow_key, $smart_platina_keys)) {
            $guaranteed_frequency = strtolower(str_replace('_', '', $flow_data->strGuaranteedPayoutFrequency ?? ''));
        }
        $sound_array = [];
        // Normalize frequency
        $payment_frequent = strtolower(str_replace(['_', ' ', '-'], ['', '_', '_'], $flow_data->FREQUENCY ?? ''));
        // Welcome screen sounds
        if (Str::contains(Str::lower(str_replace(' ', '', $screen)), 'welcomescreen')) {
           

            if (in_array($links['source'], [3, 8])) {
                $sound_array[] = 'welcome_online';
            } else {
                $retire_or_platina_keys = array_merge([
                    'sbilm_retire_smart_plus', 'sbilsa_retire_smart_plus',
                    'sbilo_retire_smart_plus', 'sbily_retire_smart_plus',
                    'sbilpl_retire_smart_plus'
                ], $smart_platina_keys);

                if (in_array($flow_key, $retire_or_platina_keys)) {
                    $sound_array[] = 'welcome';
                } elseif (in_array($flow_key, ['sbilm_rinn_raksha', 'sbilpn_rinn_raksha', 'sbilmp_rinn_raksha'])) {
                    $basePath = "/var/www/html/sbil_piwc_rinn_raksha/";
                    $loan_category = $flow_data->LOAN_CATEGORY ?? '';

                    if ($loan_category == "Personal Loan") {
                        $sound_array = ['welcome_one', 'personal_loan', 'welcome_two'];

                    } elseif ($loan_category == "Home Loan") {
                        $sound_array = ['welcome_one', 'home_loan', 'welcome_two'];
                    } else {
                        $sound_array[] = 'welcome';
                    }
                } else {
                    $sound_array[] = 'welcome';
                }
            }
        }
        


        $screen_key = strtolower(str_replace(' ', '', $screen));
        $rinn_raksha_keys = ['sbilm_rinn_raksha', 'sbilpn_rinn_raksha', 'sbilmp_rinn_raksha'];

        // Check for Rinn Raksha flow
        if (in_array($flow_key, $rinn_raksha_keys)) {
            $basePath = "D:/xampp/htdocs/sbil_piwc_rinn_raksha/";
            // $basePath = "/var/www/html/sbil_piwc_rinn_raksha/";

            // Medical Questionnaire Screen
            if (Str::contains($screen_key, 'medicalquestionnaire-disagree')) {
                $sound_array[] = '4_2';
            } elseif (Str::contains($screen_key, 'medicalquestionnaire')) {
                $sound_array[] = '5_1';
            }

            // Personal Details Screen
            if (Str::contains($screen_key, 'personaldetails-disagree')) {
                $sound_array[] = '4_2';
            } elseif (Str::contains($screen_key, 'personaldetails')) {
                $sound_array[] = '3_1';
            }

        } else {
             $basePath = "D:/xampp/htdocs/sbil_piwc/";
            // Non-Rinn Raksha flow for Personal Details
            if (Str::contains($screen_key, 'personaldetails-disagree')) {
                $sound_array[] = '3_2';
            } elseif (Str::contains($screen_key, 'personaldetails')) {
                $sound_array[] = '3_1';
            }
        }

if (strpos($screen_key, 'policydetails-disagree') !== false) {
    $sound_array[] = '4_2';
} elseif (strpos($screen_key, 'policydetails') !== false) {
    $explodedFlow = explode('_', $parameters->flow_key ?? '');

    if (count($explodedFlow) > 2) {
        // Skip the first element and join the rest with underscores
        $product_name = implode('_', array_slice($explodedFlow, 1));
    } else {
        $product_name = 'null';
    }
    $flowKey = trim($parameters->flow_key);
    $flowData = $parameters->flow_data ?? new stdClass();
    $product_name = $product_name ?? '';

        // 1. Append PLAN to product_name for Smart Platina Plus
        $smartPlatinaPlusKeys = [
            'sbilm_smart_platina_plus', 'sbilsa_smart_platina_plus', 'sbilo_smart_platina_plus',
            'sbily_smart_platina_plus', 'sbilpl_smart_platina_plus'
        ];

        if (in_array($flowKey, $smartPlatinaPlusKeys)) {
            $plan = strtolower(str_replace(' ', '_', $flowData->PLAN ?? ''));
            $product_name .= "_$plan";
        }

            // 2. Handle Smart Bachat PLAN options
            $smartBachatKeys = [
                'sbilm_smart_bachat', 'sbilsa_smart_bachat', 'sbilo_smart_bachat'
            ];

            if (in_array($flowKey, $smartBachatKeys)) {
                $plan = strtolower($flowData->PLAN ?? '');
                $option = (strpos($plan, 'option a') !== false) ? 'option_a' : 'option_b';
                $product_name .= "_$option";
            }

            // 3. Determine payment_type based on PREMIUM_POLICY_TYPE
            $premiumType = strtolower(trim($flowData->PREMIUM_POLICY_TYPE ?? ''));
            if ($premiumType === "regular") {
                $payment_type = "regular";
            } elseif ($premiumType === "limited" || $premiumType === "lppt") {
                $payment_type = "limited";
            } elseif ($premiumType === "" || $premiumType === "null") {
                $payment_type = "regular";
            } else {
                $payment_type = str_replace('_', '', $premiumType);
            }

            // 4. Smart Champ override
            $smartChampKeys = [
                'sbilm_smart_champ_insurance', 'sbilsa_smart_champ_insurance', 'sbilo_smart_champ_insurance'
            ];

            if (in_array($flowKey, $smartChampKeys) && $premiumType === "regular") {
                $payment_type = "limited";
            }

// 5. Handle cases with default 'limited' if PREMIUM_POLICY_TYPE is empty
$limitedFallbackKeys = [
    'sbilm_smart_bachat', 'sbilsa_smart_bachat', 'sbilo_smart_bachat',
    'sbilm_smart_platina_assure_v4', 'sbilsa_smart_platina_assure_v4', 'sbilo_smart_platina_assure_v4',
    'sbily_smart_platina_assure_v4', 'sbilpl_smart_platina_assure_v4',
    'sbilsa_new_smart_samridhi', 'sbilm_new_smart_samridhi', 'sbilo_new_smart_samridhi',
    'sbily_new_smart_samridhi', 'sbilm_smart_swadhan_supreme', 'sbilsa_smart_swadhan_supreme',
    'sbilo_smart_swadhan_supreme', 'sbilm_smart_bachat_plus', 'sbilsa_smart_bachat_plus',
    'sbilpl_smart_bachat_plus'
];

if (in_array($flowKey, $limitedFallbackKeys) && ($premiumType === "" || $premiumType === null)) {
    $payment_type = "limited";
}

// 6. Smart Annuity Plus - default to 'single'
$annuityPlusKeys = [
    'sbilsa_smart_annuity_plus', 'sbilm_smart_annuity_plus', 'sbilo_smart_annuity_plus',
    'sbily_smart_annuity_plus', 'sbilpl_smart_annuity_plus'
];

if (in_array($flowKey, $annuityPlusKeys) && ($premiumType === "" || $premiumType === null)) {
    $payment_type = "single";
}

// 7. Guaranteed Payout Frequency for Smart Platina Plus
if (in_array($flowKey, $smartPlatinaPlusKeys)) {
    $guaranteed_frequency = strtolower(str_replace('_', '', $flowData->strGuaranteedPayoutFrequency ?? ''));
}

// 8. Frequency formatting
$payment_frequent = strtolower(str_replace(' ', '_', str_replace('_', ' ', $flowData->FREQUENCY ?? '')));

// 9. Other common variables
$payment_term = $flowData->PAYMENT_TERM ?? '';
$payment_term_in_years = strtolower(str_replace('_', '', $flowData->payment_term_in_years ?? ''));
$benefit_term = $flowData->BENEFIT_TERM ?? '';
$policy_term_in_years = (int)str_replace('_', '', $benefit_term) + ($completed_year ?? 0);

 $data_list = $this->jobService->formatPDFCollectedData($links,json_decode($links['reg_photo_url'],TRUE),json_decode($links['consent_image_url'],TRUE),json_decode($links['response'],TRUE));


if ($lang == 'eng') {

    $retireSmartKeys = [
        'sbilm_retire_smart', 'sbilsa_retire_smart', 'sbilo_retire_smart', 'sbilpl_retire_smart',
        'sbilm_retire_smart_plus', 'sbily_retire_smart_plus', 'sbilo_retire_smart_plus',
        'sbilsa_retire_smart_plus', 'sbilpl_retire_smart_plus'
    ];

    if (in_array($parameters->flow_key, $retireSmartKeys)) {

        $sound_array[] = $product_name;
        $sound_array[] = $payment_type;

        if (strtolower($parameters->flow_data->PREMIUM_POLICY_TYPE) != "single") {
            $sound_array[] = "premium_of_new";
            $sound_array[] = $payment_frequent;
            $sound_array[] = "premium_note";
        } else {
            $sound_array[] = "premium_of_single";
            $sound_array[] = $payment_type;
            $sound_array[] = "premium_note_single";
        }

        $sound_array[] = $payment_type;
        $sound_array[] = "premium_of";

        $premium_amount = self::convertNumberToWord($parameters->flow_data->PREMIUM_AMOUNT);
        $sound_array = array_merge($sound_array, $premium_amount);
        $sound_array[] = "rupees";

        $premium_type = strtolower($parameters->flow_data->PREMIUM_POLICY_TYPE ?? '');
        $is_single_policy = ($premium_type === 'single');


        if (!$is_single_policy) {
            $sound_array[] = $payment_frequent;
            $sound_array[] = "for";

             $term = $payment_term ?? $parameters->flow_data->PAYMENT_TERM ?? 0;
             
            $payment_term_words = self::convertNumberToWord($term);
        
            if (!is_array($payment_term_words)) {
                $payment_term_words = [$payment_term_words];
            }


            $sound_array = array_merge($sound_array, $payment_term_words);
            $sound_array[] = "years";

            // Add premium_till year
            $sound_array[] = "premium_till";
            $total_term = (int)($parameters->flow_data->PAYMENT_TERM ?? 0) + $completed_year_minusOne;
            $split = str_split(trim($total_term));
            foreach ($split as $split_num) {
                $payment_term_year = self::strInLetter($split_num);
                $sound_array = array_merge($sound_array, $payment_term_year);
            }
        } else {
            $sound_array[] = "policy_term_single";
        }

           

    } else {
        // Instead of long flow_key list, use this:
        $otherFlowKeys = [/* list all other supported flow keys here */];

        $sound_array[] = $product_name;
        $sound_array[] = $payment_type;
        $sound_array[] = "premium_of";

        $premium_amount = explode('.', $parameters->flow_data->PREMIUM_AMOUNT);
        $rupees_in_words = self::convertNumberToWord($premium_amount[0]);
        $sound_array = array_merge($sound_array, $rupees_in_words);
        $sound_array[] = "rupees";

        if (!empty($premium_amount[1])) {
            $paisa_in_words = self::convertNumberToWord($premium_amount[1]);
            $sound_array[] = "and";
            $sound_array = array_merge($sound_array, $paisa_in_words);
            $sound_array[] = "paisa";
        }
            $valid_flow_keys = [
            'sbilm_smart_scholar', 'sbilsa_smart_scholar', 'sbilo_smart_scholar',
            'sbilm_smart_wealth_builder_v3', 'sbilsa_smart_wealth_builder_v3', 'sbilo_smart_wealth_builder_v3', 'sbilpl_smart_wealth_builder_v3',
            'sbilm_smart_swadhan_plus', 'sbilsa_smart_swadhan_plus', 'sbilo_smart_swadhan_plus',
            'sbilm_smart_wealth_assure', 'sbilsa_smart_wealth_assure', 'sbilo_smart_wealth_assure',
            'sbilm_smart_platina_supreme', 'sbilsa_smart_platina_supreme', 'sbilo_smart_platina_supreme', 'sbilpl_smart_platina_supreme',
            'sbilm_smart_platina_young_achiever', 'sbilsa_smart_platina_young_achiever', 'sbilo_smart_platina_young_achiever', 'sbilpl_smart_platina_young_achiever',
            'sbilm_smart_privilege_v3', 'sbilsa_smart_privilege_v3', 'sbilo_smart_privilege_v3', 'sbilpl_smart_privilege_v3',
            'sbilm_smart_champ_insurance', 'sbilsa_smart_champ_insurance', 'sbilo_smart_champ_insurance',
            'sbilm_smart_lifetime_saver', 'sbilsa_smart_lifetime_saver', 'sbilo_smart_lifetime_saver',
            'sbilm_smart_elite_v4', 'sbilsa_smart_elite_v4', 'sbilo_smart_elite_v4',
            'sbilm_smart_elite_plus', 'sbilsa_smart_elite_plus', 'sbilo_smart_elite_plus', 'sbilpl_smart_elite_plus',
            'sbilm_smart_bachat', 'sbilsa_smart_bachat', 'sbilo_smart_bachat',
            'sbilm_smart_shield', 'sbilsa_smart_shield', 'sbilo_smart_shield',
            'sbilm_sampoorn_cancer_suraksha', 'sbilsa_sampoorn_cancer_suraksha', 'sbilo_sampoorn_cancer_suraksha',
            'sbilm_saral_jeevan_bima', 'sbilsa_saral_jeevan_bima', 'sbilo_saral_jeevan_bima', 'sbily_saral_jeevan_bima',
            'sbilm_eshield_next', 'sbilsa_eshield_next', 'sbilo__eshield_next',
            'sbilm_smart_insure_wealth_plus', 'sbilsa_smart_insure_wealth_plus', 'sbilo_smart_insure_wealth_plus',
            'sbilm_smart_money_back_gold', 'sbilsa_smart_money_back_gold', 'sbilo_smart_money_back_gold',
            'sbilm_smart_annuity_plus', 'sbilsa_smart_annuity_plus', 'sbilo_smart_annuity_plus', 'sbily_smart_annuity_plus', 'sbilpl_smart_annuity_plus',
            'sbilm_smart_money_planner', 'sbilsa_smart_money_planner', 'sbilo_smart_money_planner',
            'sbilm_new_smart_samridhi', 'sbilsa_new_smart_samridhi', 'sbilo_new_smart_samridhi', 'sbily_new_smart_samridhi',
            'sbilm_smart_humsafar', 'sbilsa_smart_humsafar', 'sbilo_smart_humsafar',
            'sbilm_saral_retirement_saver', 'sbilsa_saral_retirement_saver', 'sbilo_saral_retirement_saver',
            'sbilm_shudh_nivesh', 'sbilsa_shudh_nivesh', 'sbilo_shudh_nivesh',
            'sbilm_smart_income_protect', 'sbilsa_smart_income_protect', 'sbilo_smart_income_protect',
            'sbilm_saral_insure_wealth_plus', 'sbilsa_saral_insure_wealth_plus', 'sbilo_saral_insure_wealth_plus',
            'sbilm_smart_future_choice', 'sbilsa_smart_future_choice', 'sbilo_smart_future_choice',
            'sbilm_smart_future_star', 'sbilsa_smart_future_star', 'sbilo_smart_future_star', 'sbilpl_smart_future_star',
            'sbilm_saral_pension', 'sbilsa_saral_pension', 'sbilo_saral_pension',
            'sbilm_smart_platina_plus', 'sbilsa_smart_platina_plus', 'sbilo_smart_platina_plus', 'sbily_smart_platina_plus', 'sbilpl_smart_platina_plus ',
            'sbilm_smart_swadhan_supreme', 'sbilsa_smart_swadhan_supreme', 'sbilo_smart_swadhan_supreme',
            'sbilm_saral_swadhan_supreme', 'sbilsa_saral_swadhan_supreme', 'sbilo_saral_swadhan_supreme',
            'sbilm_smart_scholar_plus', 'sbilsa_smart_scholar_plus', 'sbilo_smart_scholar_plus', 'sbily_smart_scholar_plus', 'sbilpl_smart_scholar_plus',
            'sbilm_smart_shield_premier', 'sbilsa_smart_shield_premier', 'sbilo_smart_shield_premier', 'sbilpl_smart_shield_premier',
            'sbilm_smart_fortune_builder', 'sbilsa_smart_fortune_builder', 'sbilo_smart_fortune_builder', 'sbily_smart_fortune_builder', 'sbilpl_smart_fortune_builder',
            'sbilm_smart_privilege_plus', 'sbilsa_smart_privilege_plus', 'sbilo_smart_privilege_plus', 'sbilpl_smart_privilege_plus',
            'sbilo_ewealth_plus',
            'sbilm_smart_swadhan_neo', 'sbilsa_smart_swadhan_neo', 'sbilo_smart_swadhan_neo', 'sbilpl_smart_swadhan_neo',
            'sbilm_smart_bachat_plus', 'sbilsa_smart_bachat_plus', 'sbilpl_smart_bachat_plus'
            ];

            if (in_array($parameters->flow_key, $valid_flow_keys)) {
            if ($payment_type == "single") {
            $annuity_keys = [
                'sbilm_smart_annuity_plus', 'sbilsa_smart_annuity_plus', 'sbilo_smart_annuity_plus', 
                'sbily_smart_annuity_plus', 'sbilpl_smart_annuity_plus',
                'sbilm_saral_pension', 'sbilsa_saral_pension', 'sbilo_saral_pension'
            ];

            if (in_array($parameters->flow_key, $annuity_keys)) {
                  $sound_array[] ='policy_term_single';
            }
            } else {

                $sound_array[] = $payment_frequent;
                $sound_array[] = "for";

                $payment_term_value = $parameters->flow_data->PAYMENT_TERM;

                // Handle null or empty PAYMENT_TERM
                if (empty($payment_term_value)) {
                    $payment_term = ["zero"];
                } else {
                    $payment_term = self::convertNumberToWord($payment_term_value);
                }

                $sound_array = array_merge($sound_array, $payment_term);
                $sound_array[] = "years";

                $sound_array[] = "premium_till";

                // Add PAYMENT_TERM and completed_year_minusOne
                $total_years = (int)$payment_term_value + $completed_year_minusOne;
                $split = str_split((string)trim($total_years));

                foreach ($split as $split_num) {
                    $payment_term_year = self::strInLetter($split_num);
                    $sound_array = array_merge($sound_array, $payment_term_year);
                }


            }
            } else {
                    $sound_array[] = $payment_frequent;
                    $sound_array[] = "for";

                    $payment_term_value = $parameters->flow_data->PAYMENT_TERM;

                    // If empty or null, assign "zero", else convert number to word
                    if (empty($payment_term_value)) {
                        $payment_term = ["zero"];
                    } else {
                        $payment_term = self::convertNumberToWord($payment_term_value);
                    }

                    $sound_array = array_merge($sound_array, $payment_term);
                    $sound_array[] = "years";

                    $sound_array[] = "premium_till";

                    // Calculate total years and split into digits
                    $total_years = (int)$payment_term_value + $completed_year_minusOne;
                    $split_digits = str_split((string)$total_years);

                    foreach ($split_digits as $digit) {
                        $sound_array = array_merge($sound_array, self::strInLetter($digit));
                    }
                }



    }
}

}
        // medical questionaire
        $excluded_flow_keys = [ 'sbilm_retire_smart',
            'sbilo_retire_smart',
            'sbilsa_retire_smart',
            'sbilpl_retire_smart',
            'sbilm_saral_pension',
            'sbilsa_saral_pension',
            'sbilpl_saral_pension',
            'sbilpl_annuity_plus',
            'sbilsa_annuity_plus',
            'sbilo_annuity_plus',
            'sbilpl_smart_annuity_plus',
            'sbilm_annuity_plus',
            'sbilm_retire_smart_plus',
            'sbily_retire_smart_plus',
            'sbilo_retire_smart_plus',
            'sbilsa_retire_smart_plus',
            'sbilpl_retire_smart_plus'
        ];

        if (!in_array($parameters->flow_key, $excluded_flow_keys)) {
            $clean_screen = strtolower(str_replace(" ", "", $screen));

            // $sound_array = [];

            if (strpos($clean_screen, 'medicalquestionnaire-disagree') !== false) {
                $sound_array[] = '4_2';
            } elseif (strpos($clean_screen, 'medicalquestionnaire') !== false) {
                $sound_array[] = '5_1';
            }
        }
        // benefitillustration
        $screen_cleaned = strtolower(str_replace(" ", "", $screen));

if (strpos($screen_cleaned, 'benefitillustration-disagree') !== false) {
    $sound_array = array('4_2');
} else if (strpos($screen_cleaned, 'benefitillustration') !== false) {
    $sound_array = array();

    $flow_key = $parameters->flow_key;

    $platina_keys = array(
        'sbilm_smart_platina_plus',
        'sbilsa_smart_platina_plus',
        'sbilo_smart_platina_plus',
        'sbily_smart_platina_plus',
        'sbilpl_smart_platina_plus',
        'sbilo_smart_platina_supreme',
        'sbilpl_smart_platina_supreme',
        'sbilm_smart_platina_supreme',
        'sbilsa_smart_platina_supreme'
    );

    $other_keys = array(
        'sbilm_smart_wealth_builder_v3','sbilsa_smart_wealth_builder_v3',
        'sbilo_smart_wealth_builder_v3',
        'sbilm_smart_swadhan_plus',
        'sbilsa_smart_swadhan_plus',
        'sbilo_smart_swadhan_plus',
        'sbilm_saral_swadhan_plus_v3',
        'sbilsa_saral_swadhan_plus_v3',
        'sbilpl_smart_wealth_builder_v3',
        'sbilo_saral_swadhan_plus_v3',
       'sbilm_smart_scholar',
       'sbilsa_smart_scholar',
       'sbilo_smart_scholar',
       'sbilm_smart_lifetime_saver',
       'sbilsa_smart_lifetime_saver',
       'sbilo_smart_lifetime_saver',
       'sbilm_smart_wealth_assure',
       'sbilsa_smart_wealth_assure',
       'sbilo_smart_wealth_assure',
       'sbilm_smart_elite_v4',
       'sbilsa_smart_elite_v4',
       'sbilo_smart_elite_v4',
       'sbilm_smart_elite_plus',
       'sbilsa_smart_elite_plus',
       'sbilo_smart_elite_plus',
       'sbilpl_smart_elite_plus',
       'sbilm_smart_bachat_plus',
       'sbilsa_smart_bachat_plus',
       'sbilpl_smart_bachat_plus',
       'sbilm_smart_privilege_v3',
       'sbilsa_smart_privilege_v3',
       'sbilpl_smart_privilege_v3',
       'sbilo_smart_privilege_v3',
       'sbilm_smart_champ_insurance',
       'sbilsa_smart_champ_insurance',
       'sbilo_smart_champ_insurance',
       'sbilm_smart_bachat',
       'sbilsa_smart_bachat',
       'sbilo_smart_bachat',
       'sbilm_smart_shield',
       'sbilsa_smart_shield',
       'sbilo_smart_shield',
       'sbilm_smart_power_insurance',
       'sbilsa_smart_power_insurance',
       'sbilpl_smart_power_insurance',
       'sbilo_smart_power_insurance',
       'sbilm_sampoorn_cancer_suraksha',
       'sbilsa_sampoorn_cancer_suraksha',
       'sbilo_sampoorn_cancer_suraksha',
       'sbilm_saral_jeevan_bima',
       'sbilsa_saral_jeevan_bima',
       'sbilo_saral_jeevan_bima',
       'sbily_saral_jeevan_bima',
       'sbilm_eshield_next',
       'sbilsa_eshield_next',
       'sbilo__eshield_next',
       'sbilm_smart_insure_wealth_plus',
       'sbilsa_smart_insure_wealth_plus',
       'sbilo_smart_insure_wealth_plus',
       'sbilm_smart_money_back_gold',
       'sbilsa_smart_money_back_gold',
       'sbilo_smart_money_back_gold',
       'sbilm_smart_money_planner',
       'sbilsa_smart_money_planner',
       'sbilo_smart_money_planner',
       'sbilm_new_smart_samridhi',
       'sbilsa_new_smart_samridhi',
       'sbilo_new_smart_samridhi',
       'sbily_new_smart_samridhi',
       'sbilm_smart_humsafar',
       'sbilsa_smart_humsafar',
       'sbilo_smart_humsafar',
       'sbilm_saral_retirement_saver',
       'sbilsa_saral_retirement_saver',
       'sbilo_saral_retirement_saver',
       'sbilm_smart_annuity_plus',
       'sbilsa_smart_annuity_plus',
       'sbilo_smart_annuity_plus',
       'sbily_smart_annuity_plus',
       'sbilpl_smart_annuity_plus',
       'sbilm_shudh_nivesh',
       'sbilsa_shudh_nivesh',
       'sbilo_shudh_nivesh',
       'sbilm_smart_income_protect',
       'sbilsa_smart_income_protect',
       'sbilo_smart_income_protect',
       'sbilm_saral_insure_wealth_plus',
       'sbilsa_saral_insure_wealth_plus',
       'sbilo_saral_insure_wealth_plus',
       'sbilm_smart_future_choice',
       'sbilsa_smart_future_choice',
       'sbilo_smart_future_choice',
       'sbilm_smart_future_star',
       'sbilsa_smart_future_star',
       'sbilo_smart_future_star',
       'sbilpl_smart_future_star',
       'sbilm_saral_pension',
       'sbilsa_saral_pension',
       'sbilo_saral_pension',
       'sbilm_smart_swadhan_supreme',
       'sbilsa_smart_swadhan_supreme',
       'sbilo_smart_swadhan_supreme',
       'sbilm_saral_swadhan_supreme',
       'sbilsa_saral_swadhan_supreme',
       'sbilo_saral_swadhan_supreme',
       'sbilm_smart_scholar_plus',
       'sbilsa_smart_scholar_plus',
       'sbily_smart_scholar_plus',
       'sbilo_smart_scholar_plus',
       'sbilpl_smart_scholar_plus',
       'sbilm_smart_shield_premier',
       'sbilpl_smart_shield_premier',
       'sbilsa_smart_shield_premier',
       'sbilo_smart_shield_premier',
       'sbilm_smart_fortune_builder',
       'sbilsa_smart_fortune_builder',
       'sbilpl_smart_fortune_builder',
       'sbilo_smart_fortune_builder',
       'sbily_smart_fortune_builder',
       'sbilo_smart_privilege_plus',
       'sbilm_smart_privilege_plus',
       'sbilsa_smart_privilege_plus',
       'sbilpl_smart_privilege_plus',
       'sbilm_smart_swadhan_neo',
       'sbilsa_smart_swadhan_neo',
       'sbilpl_smart_swadhan_neo',
       'sbilo_smart_swadhan_neo',
       'sbilm_smart_platina_young_achiever',
       'sbilsa_smart_platina_young_achiever',
       'sbilpl_smart_platina_young_achiever',
       'sbilo_smart_platina_young_achiever',
    );

    $retireSmartFlows = [
    'sbilm_retire_smart',
    'sbilo_retire_smart',
    'sbilsa_retire_smart',
    'sbilm_retire_smart_plus',
    'sbilo_retire_smart_plus',
    'sbilsa_retire_smart_plus',
    'sbily_retire_smart_plus',
    'sbilpl_retire_smart',
    'sbilpl_retire_smart_plus'
];


    if (in_array($flow_key, $platina_keys)) {
        $sound_array[] = '6_6';
    } elseif (in_array($flow_key, $other_keys)) {
        $sound_array[]  = '6_3';   
    } elseif (in_array($parameters->flow_key, $retireSmartFlows)) {
            $sound_array[] = '6_1';

            $term = trim($parameters->flow_data->BENEFIT_TERM) + $completed_year;
            foreach (str_split($term) as $digit) {
                $payment_term_year = self::strInLetter($digit); 
                $sound_array = array_merge($sound_array, $payment_term_year);    
            }

            $sound_array[] = '6_2';
            $sound_array[] = '6_3';
    } else {
            $smart_platina_flows = [
            'sbilm_smart_platina_assure_v4',
            'sbilsa_smart_platina_assure_v4',
            'sbilo_smart_platina_assure_v4',
            'sbily_smart_platina_assure_v4',
            'sbilpl_smart_platina_assure_v4'
            ];

            $smart_champ_flows = [
            'sbilm_smart_champ_insurance',
            'sbilsa_smart_champ_insurance',
            'sbilo_smart_champ_insurance'
            ];
            $excluded_flow_keys = [
    'sbilm_retire_smart', 'sbilo_retire_smart', 'sbilsa_retire_smart', 'sbilm_retire_smart_plus',
    'sbilo_retire_smart_plus', 'sbily_retire_smart_plus', 'sbilsa_retire_smart_plus',
    'sbilm_smart_platina_plus', 'sbilpl_smart_platina_plus', 'sbilo_smart_platina_plus',
    'sbily_smart_platina_plus', 'sbilsa_smart_platina_plus', 'sbilpl_retire_smart',
    'sbilpl_retire_smart_plus', 'sbilpl_smart_platina_plus', 'sbilo_smart_platina_supreme',
    'sbilpl_smart_platina_supreme', 'sbilm_smart_platina_supreme', 'sbilsa_smart_platina_supreme',
    'sbilm_smart_platina_young_achiever', 'sbilsa_smart_platina_young_achiever', 'sbilpl_smart_platina_young_achiever',
    'sbilo_smart_platina_young_achiever'
];

$excludedFlowKeys = [
    'sbilm_smart_wealth_builder_v3', 'sbilsa_smart_wealth_builder_v3', 'sbilo_smart_wealth_builder_v3', 'sbilpl_smart_wealth_builder_v3',
    'sbilm_saral_swadhan_plus_v3', 'sbilsa_saral_swadhan_plus_v3', 'sbilo_saral_swadhan_plus_v3',
    'sbilm_smart_scholar', 'sbilsa_smart_scholar', 'sbilo_smart_scholar',
    'sbilm_smart_wealth_assure', 'sbilsa_smart_wealth_assure', 'sbilo_smart_wealth_assure',
    'sbilm_smart_privilege_v3', 'sbilsa_smart_privilege_v3', 'sbilo_smart_privilege_v3',
    'sbilm_smart_elite_v4', 'sbilsa_smart_elite_v4', 'sbilo_smart_elite_v4',
    'sbilm_smart_elite_plus', 'sbilsa_smart_elite_plus', 'sbilo_smart_elite_plus', 'sbilpl_smart_elite_plus',
    'sbilm_smart_champ_insurance', 'sbilsa_smart_champ_insurance', 'sbilo_smart_champ_insurance',
    'sbilm_smart_platina_assure_v4', 'sbilpl_smart_platina_assure_v4', 'sbilsa_smart_platina_assure_v4', 'sbily_smart_platina_assure_v4', 'sbilo_smart_platina_assure_v4',
    'sbilm_smart_money_planner', 'sbilsa_smart_money_planner', 'sbilo_smart_money_planner',
    'sbilm_new_smart_samridhi', 'sbilsa_new_smart_samridhi', 'sbilo_new_smart_samridhi', 'sbily_new_smart_samridhi',
    'sbilm_smart_humsafar', 'sbilsa_smart_humsafar', 'sbilo_smart_humsafar',
    'sbilm_saral_retirement_saver', 'sbilsa_saral_retirement_saver', 'sbilo_saral_retirement_saver',
    'sbilm_smart_annuity_plus', 'sbilsa_smart_annuity_plus', 'sbilpl_smart_annuity_plus', 'sbilo_smart_annuity_plus', 'sbily_smart_annuity_plus',
    'sbilm_shudh_nivesh', 'sbilsa_shudh_nivesh', 'sbilo_shudh_nivesh',
    'sbilm_smart_income_protect', 'sbilsa_smart_income_protect', 'sbilo_smart_income_protect',
    'sbilm_saral_insure_wealth_plus', 'sbilsa_saral_insure_wealth_plus', 'sbilo_saral_insure_wealth_plus',
    'sbilm_smart_future_choice', 'sbilsa_smart_future_choice', 'sbilo_smart_future_choice',
    'sbilm_smart_future_star', 'sbilsa_smart_future_star', 'sbilo_smart_future_star', 'sbilpl_smart_future_star',
    'sbilm_saral_pension', 'sbilsa_saral_pension', 'sbilo_saral_pension',
    'sbilm_smart_scholar_Plus', 'sbilsa_smart_scholar_plus', 'sbilo_smart_scholar_plus', 'sbily_smart_scholar_plus', 'sbilpl_smart_scholar_plus',
    'sbilm_smart_fortune_builder', 'sbilsa_smart_fortune_builder', 'sbilpl_smart_fortune_builder', 'sbilo_smart_fortune_builder', 'sbily_smart_fortune_builder',
    'sbilm_smart_privilege_plus', 'sbilsa_smart_privilege_plus', 'sbilpl_smart_privilege_plus', 'sbilo_smart_privilege_plus',
    'sbilm_smart_shield_premier', 'sbilsa_smart_shield_premier', 'sbilpl_smart_shield_premier', 'sbilo_smart_shield_premier',
];

            if (!in_array($parameters->flow_key, $smart_platina_flows)) {
                $sound_array[] = '6_1';

                if (!in_array($parameters->flow_key, $smart_champ_flows)) {
                $term_value = trim($parameters->flow_data->BENEFIT_TERM) + $completed_year;
                $split = str_split($term_value);

                foreach ($split as $split_num) {
                    $payment_term_year = self::strInLetter($split_num); 
                    $sound_array = array_merge($sound_array, $payment_term_year);    
                }
                }

                $sound_array[] = '6_2';
            } 
            // Check if flow_key is not in the excluded list
            if (!in_array($parameters->flow_key, $excluded_flow_keys)) {
                if (!in_array($parameters->flow_key, $excludedFlowKeys)) {
                    $BI_4 = self::convertNumberToWord(trim($parameters->flow_data->BI_4)); 
                    $sound_array = array_merge($sound_array, $BI_4); 
                    $sound_array[] = "rupees";
                }
                 $sound_array[] = '6_3';
            }

    }

    }

// Normalize screen value
$cleaned_screen = strtolower(str_replace(' ', '', $screen));


// Prioritize disagree cases
if (strpos($cleaned_screen, 'deathbenefits-disagree') !== false || 
    strpos($cleaned_screen, 'productbenefits-disagree') !== false) {
    
    $sound_array[] = '4_2';

} else {
    // Match for benefit screens
    if (strpos($cleaned_screen, 'deathbenefits') !== false || 
        strpos($cleaned_screen, 'productbenefits') !== false) {
        $sound_array[] = 'DB'; // Or 'PB' if needed based on screen
    }

    // Match for important points or terms details
    if (strpos($cleaned_screen, 'importantpoints') !== false || 
        strpos($cleaned_screen, 'termsdetails') !== false) {
        $sound_array[] = '11_1';
    }

    // Match for final confirmation
    if (strpos($cleaned_screen, 'finalconfirmationscreen') !== false) {
        $sound_array[] = 'vid_online';
    }
}


       
        $retire_smart_keys = [
            "sbilm_retire_smart", "sbilsa_retire_smart", "sbilo_retire_smart",
            "sbilm_retire_smart_plus", "sbilsa_retire_smart_plus", "sbilo_retire_smart_plus",
            "sbily_retire_smart_plus", "sbilpl_retire_smart", "sbilpl_retire_smart_plus"
        ];

        if (!in_array($parameters->flow_key, $retire_smart_keys)) {
            define('PRODUCT_AUDIO_LANG_PATH_NO', $basePath . 'assets/product_assets/' . $parameters->flow_key . '/audio/' . $lang . '/scenes/');
        } else {
            define('PRODUCT_AUDIO_LANG_PATH_NO', $basePath . 'assets/audio/product/' . $lang . '/scenes/');
        }

        define('COMMON_AUDIO_LANG_PATH_NO', $basePath . 'assets/audio/product/' . $lang . '/common/');
// print_r(COMMON_AUDIO_LANG_PATH_NO);die;

   $audio_array = [];     
// 1. Product audio fixed keys
$product_keys = [
    'welcome', 'welcome_one', 'welcome_two', 'home_loan', 'personal_loan', 'welcome_online',
    'welcome_plus', 'vid_online', 'policy_term_single', 'premium_of_single', 'dis_aud',
    '3_1', '6_3_1', '6_2_regular', '6_2_single', '3_2', '4_2', '5_1', '6_1', '6_2', '6_3',
    '6_4', '6_5', '6_6', '6_7', 'PB', '11_1', 'DB'
];

foreach ($product_keys as $key) {
    $audio_array[$key] = PRODUCT_AUDIO_LANG_PATH_NO . $key . '.mp3';
}

    // 2. Dynamic product keys
    if (!empty($guaranteed_frequency)) {
        $product_dynamic_keys = [
            $guaranteed_frequency => PRODUCT_AUDIO_LANG_PATH_NO . $guaranteed_frequency . '.mp3'
        ];
        $audio_array = array_merge($audio_array, $product_dynamic_keys);
    }



    // print_r($payment_type);die;
    // 3. Common audio with dynamic variables   !empty($payment_term)|| !empty($payment_term_in_years)|| !empty($benefit_term)|| !empty($policy_term_in_years)
    if (!empty($product_name) || !empty($payment_type)) { 
        $common_dynamic_keys = [
            $product_name, $payment_type, $payment_frequent, $payment_term, $payment_term_in_years,
            $benefit_term, $policy_term_in_years
        ];



        if (array_filter($common_dynamic_keys)) {
        foreach ($common_dynamic_keys as $key) {
            if (!empty($key)) {
                $audio_array[$key] = COMMON_AUDIO_LANG_PATH_NO . $key . '.mp3';
            }
        }
        }
    }
       

// 4. Common static keys
$common_keys = [
    'premium_of', 'req_tax', 'applicable_tax', 'for', 'premium_till', 'policy_term',
    'payable_till', 'sum_assured', 'policy_payment_disclaimer', 'years', 
    'marathi_policy_extra_audio', 'zero', 'one', 'two', 'three', 'four', 'five', 'six',
    'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen',
    'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen', 'twenty', 'thirty',
    'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety', 'hundred', 'thousand',
    'lakh', 'crore', 'hundreds', 'thousands', 'lakhs', 'million', 'crores', 'rupees',
    'and', 'paisa'
];

foreach ($common_keys as $key) {
    $audio_array[$key] = COMMON_AUDIO_LANG_PATH_NO . $key . '.mp3';
}


$retireSmartKeys = [
    'sbilm_retire_smart',
    'sbilsa_retire_smart',
    'sbilo_retire_smart',
    'sbilm_retire_smart_plus',
    'sbilsa_retire_smart_plus',
    'sbilo_retire_smart_plus',
    'sbily_retire_smart_plus',
    'sbilpl_retire_smart',
    'sbilpl_retire_smart_plus'
];

if (in_array($parameters->flow_key, $retireSmartKeys)) {
    $audio_array['policy_term_single']    = COMMON_AUDIO_LANG_PATH_NO . 'policy_term_single.mp3';
    $audio_array['premium_of_single']     = COMMON_AUDIO_LANG_PATH_NO . 'premium_of_single.mp3';
    $audio_array['premium_of_new']        = COMMON_AUDIO_LANG_PATH_NO . 'premium_of_new.mp3';
    $audio_array['premium_note']          = COMMON_AUDIO_LANG_PATH_NO . 'premium_note.mp3';
    $audio_array['premium_note_single']   = COMMON_AUDIO_LANG_PATH_NO . 'premium_note_single.mp3';
    $audio_array['for_single']            = COMMON_AUDIO_LANG_PATH_NO . 'for_single.mp3';
    $audio_array['premium']               = COMMON_AUDIO_LANG_PATH_NO . 'premium.mp3';
}


for ($i = 21; $i <= 99; $i++) {
    $key = self::convertNumberToWord($i)[0]; //print_r($key[0]);//die;
    $audio_array[$key] = COMMON_AUDIO_LANG_PATH_NO . $key . '.mp3';
}
// print_r($audio_array);die;

$smart_platina_plus_flows = [
    'sbilm_smart_platina_plus',
    'sbilsa_smart_platina_plus',
    'sbilo_smart_platina_plus',
    'sbily_smart_platina_plus',
    'sbilpl_smart_platina_plus'
];

if (in_array($parameters->flow_key, $smart_platina_plus_flows)) {
    $audio_array['sum_assured'] = PRODUCT_AUDIO_LANG_PATH_NO . 'sum_assured.mp3';

    if (!empty($product_name)) {
        $audio_array[$product_name] = PRODUCT_AUDIO_LANG_PATH_NO . $product_name . '.mp3';
    }

    $audio_array['policy_payment_disclaimer'] = PRODUCT_AUDIO_LANG_PATH_NO . 'policy_payment_disclaimer.mp3';
    $audio_array['premium_of_new'] = COMMON_AUDIO_LANG_PATH_NO . 'premium_of_new.mp3';
    $audio_array['premium_note'] = COMMON_AUDIO_LANG_PATH_NO . 'premium_note.mp3';
    $audio_array['premium_note_single'] = COMMON_AUDIO_LANG_PATH_NO . 'premium_note_single.mp3';
    $audio_array['premium'] = COMMON_AUDIO_LANG_PATH_NO . 'premium.mp3';
}

   $prepare_audioArray   = array(); 
      $iset =0;
      
      $audio_html = '<!DOCTYPE html>
      <html>
      <body style="width: 99%;text-align: center;">
      <style>
      @media screen and (min-device-width: 800px) and (max-device-width: 1600px) {
        .topimg { width: 30% !important; }
      }
      .img-playbtn {
        position: absolute;
        top: 50%;
        left: 46%;
      }
      </style>';

      // Convert screen name to lowercase and remove spaces
      $screen_clean = strtolower(str_replace(" ", "", $screen));

      // Map screen keys to corresponding image keys
      $screen_map = [
        'welcomescreen' => 'Welcome Screen',
        'personaldetails-disagree' => 'Personal Details - Disagree',
        'personaldetails' => 'Personal Details',
        'policydetails-disagree' => 'Policy Details - Disagree',
        'policydetails' => 'Policy Details',
        'medicalquestionnaire-disagree' => 'Medical Questionnaire - Disagree',
        'medicalquestionnaire' => 'Medical Questionnaire',
        'medicalconfirmationscreenone' => 'Medical Confirmation Screen One',
        'medicalconfirmationccreentwo' => 'Medical Confirmation Screen Two',
        'benefitillustration-disagree' => 'Benefit Illustration - Disagree',
        'benefitillustration' => 'Benefit Illustration',
        'finalconfirmationscreen' => 'Final Confirmation Screen',
        'deathbenefits-disagree' => 'Product Benefits - Disagree',
        'productbenefits-disagree' => 'Product Benefits - Disagree',
        'deathbenefits' => 'Product Benefits',
        'productbenefits' => 'Product Benefits',
        'importantpoints' => 'Terms Details',
        'termsdetails' => 'Terms Details',
      ];

      // Loop through the map and match the screen
      foreach ($screen_map as $key => $image_key) {
        if (strpos($screen_clean, strtolower(str_replace(" ", "", $key))) !== false) {
          if (isset($data_list[$image_key]['image']['media_screen_url'])) {
            $image_url = $data_list[$image_key]['image']['media_screen_url'];
            $audio_html .= '<img class="topimg" src="' . $image_url . '" style="width: 100%; margin: 0 auto;" />';
          }
          break;
        }
      }
      

      // Add play button
      $audio_html .= '<div class="img-playbtn" id="img-playbtn">
        <img src="https://pivc.sbilife.co.in/sbil_piwc/assets/images/common/playbtn.png" style="width: 78px;" onclick="play()" />
      </div>
      <script>
      function play() {
        document.getElementById("img-playbtn").style.display = "none";
      ';

      // Handle audio play sequence
      $previous_index = null;
      $key_index = 0;
      
      foreach ($sound_array as $key => $audioStr) {
        if (isset($audio_array[$audioStr]) && file_exists($audio_array[$audioStr])) {
          $audio_update = str_replace('D:/xampp/htdocs', 'http://localhost', $audio_array[$audioStr]);
        //   $audio_update = str_replace('public_html', '', $audio_update);print_r($audio_update);
     /*      $audio_update = str_replace('/var/www/html', 'https://pivc.sbilife.co.in', $audio_array[$audioStr]);
          $audio_update = str_replace('public_html', '', $audio_update); */
          $index_var = 'index' . $key_index;

          $audio_html .= "var $index_var = new Audio(\"$audio_update\");\n";

          if ($previous_index === null) {
            $audio_html .= "$index_var.play().catch(e => {
              alert('Playback failed. Click again or check permissions.');
              document.getElementById('img-playbtn').style.display = 'block';
            });\n";
          } else {
            $audio_html .= "$previous_index.onended = function() { $index_var.play(); };\n";
          }

          $previous_index = $index_var;
          $key_index++;
        }
      }

      $audio_html .= '}
      </script>
      </body>
      </html>';

      echo $audio_html;
      die;

        // print_r($audio_array);
    }
    
    public function convertNumberToWord($num = false)
{
    $num = str_replace([',', ' '], '', trim($num));
    if (!$num) return false;

    $num = (int)$num;
    $number = $num;
    $no = floor($number);
    $point = round($number - $no, 2) * 100;

    $wordsMap = [
        '0' => '', '1' => 'one', '2' => 'two', '3' => 'three', '4' => 'four',
        '5' => 'five', '6' => 'six', '7' => 'seven', '8' => 'eight', '9' => 'nine',
        '10' => 'ten', '11' => 'eleven', '12' => 'twelve', '13' => 'thirteen',
        '14' => 'fourteen', '15' => 'fifteen', '16' => 'sixteen',
        '17' => 'seventeen', '18' => 'eighteen', '19' => 'nineteen',
        '20' => 'twenty', '30' => 'thirty', '40' => 'forty', '50' => 'fifty',
        '60' => 'sixty', '70' => 'seventy', '80' => 'eighty', '90' => 'ninety'
    ];

    $digits = ['', 'hundred', 'thousand', 'lakh', 'crore'];
    $str = [];
    $i = 0;

    while ($i < strlen($no)) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += ($divider == 10) ? 1 : 2;

        if ($number) {
            $counter = count($str);
            if ($number < 21) {
                $str[] = $wordsMap[$number] . ' ' . $digits[$counter];
            } else {
                $str[] = $wordsMap[floor($number / 10) * 10] . ' ' . $wordsMap[$number % 10] . ' ' . $digits[$counter];
            }
        } else {
            $str[] = null;
        }
    }

    $str = array_reverse($str);
    $resultWords = array_filter(array_map('trim', $str));

    // Optional: Add paise (decimal) part
    if ($point) {
        $pointWords = trim($wordsMap[floor($point / 10) * 10] . ' ' . $wordsMap[$point % 10]);
        $resultWords[] = 'and';
        $resultWords[] = $pointWords;
        $resultWords[] = 'paise';
    }

    return array_values($resultWords); // Return a clean array
}
public function strInLetter($str)
{
    if ($str === null) return [];

    $digitMap = [
        "0" => "zero", "1" => "one", "2" => "two", "3" => "three",
        "4" => "four", "5" => "five", "6" => "six", "7" => "seven",
        "8" => "eight", "9" => "nine"
    ];

    $result = [];

    $str = strtolower($str);
    $length = strlen($str);

    for ($i = 0; $i < $length; $i++) {
        $char = $str[$i];
        
        if (ctype_alpha($char)) {
            $result[] = $char; // keep letter as is
        } elseif (ctype_digit($char)) {
            $result[] = $digitMap[$char];
        }
        // Optional: handle dot, comma, dash, etc.
        // elseif ($char == '.') $result[] = 'dot';
    }

    return $result;
}

}
