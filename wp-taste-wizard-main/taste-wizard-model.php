<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<?php
class TasteWizardModel {
    public static $stepsDefintion = array(
        'nivel' => array(
            'question' => 'Queremos estar seguros de hablar tu mismo idioma, Para eso, ¿como te consideras?',
            'selection_limit' => 1,
            'options' => array(
                array("value" => 'rookie', "label" => "Novato con el Vino."),
                array("value" => 'intermediary', "label" => "Intermedio, pero quiero aprender más."),
                array("value" => 'expert', "label" => "Experto, se de lo que hablo."),
            ),
        ),
        'bebida_verano' => array(
            'question' => 'Es tarde de verano y estás sediente ¿Qué opción te apetece más?',
            'selection_limit' => 1,
            'options' => array(
                array("value" => 'limonada', "label" => "Limonada recién preparada."),
                array("value" => 'agua_de_coco', "label" => "Agua de Coco."),
                array("value" => 'malteada', "label" => "Malteada.")
            ),
        ),
        'cocktails' => array(
            'question' => 'Llegas a una fiesta a la que te invito tu amiga y el bar ofrece estos cócteles/tragos pero solo puedes escoger tres para toda la noche.',
            'selection_limit' => 3,
            'options' => array(
                array("value" => 'pina_colada', "label" => "Piña Colada"),
                array("value" => 'margarita_maracuya', "label" => "Margarita de Maracuyá"),
                array("value" => 'mojito', "label" => "Mojito"),
                array("value" => 'mimosa', "label" => "Mimosa"),
                array("value" => 'martini', "label" => "Martini"),
                array("value" => 'wiskey_rum', "label" => "Whiskey/Ron"),
            ),
        ),
        'fruta' => array(
            'question' => 'Tienes acceso a un árbol mágico que te permite cultivar solo tres tipos de frutas de por vida. ¿Cuales escoges?',
            'selection_limit' => 3,
            'options' => array(
                array("value" => 'limon_naranja', "label" => "Limón/Naranja"),
                array("value" => 'manzana_roja', "label" => "Manzana Roja & Pera"),
                array("value" => 'mango', "label" => "Mango"),
                array("value" => 'melon', "label" => "Melón"),
                array("value" => 'pineapple', "label" => "Piña"),
                array("value" => 'ciruela', "label" => "Ciruela"),
                array("value" => 'fresa', "label" => "Fresa"),
                array("value" => 'uva_roja', "label" => "Uva Roja"),
            ),
        ),
        'chocolate' => array(
            'question' => 'Imagina que las calorías no son una preocupación y puedes comer todo el chocolate que quieras pero solo puedes escoger uno, ¿Cuál escoges?',
            'selection_limit' => 1,
            'options' => array(
                array("value" => 'blanco', "label" => "Blanco"),
                array("value" => 'leche', "label" => "Leche"),
                array("value" => 'cacao', "label" => "Cacao (Oscuro)"),
            ),
        ),
        'tostado' => array(
            'question' => '¿Como te gusta tu pan en el desayuno?',
            'selection_limit' => 1,
            'options' => array(
                array("value" => 'sin_tostar', "label" => "Sin Tostar"),
                array("value" => 'tostado_normal', "label" => "Tostado Normal"),
                array("value" => 'muy_tostado', "label" => "Muy Tostado"),
            ),
        ),
        'mantequilla' => array(
            'question' => 'Si no tuvieras que preocuparte por tus calorias, cuanta mantequilla te gustaria con tu pan.',
            'selection_limit' => 1,
            'options' => array(
                array("value" => 'sin_mantequilla', "label" => "Sin mantequilla"),
                array("value" => 'poquito', "label" => "Un Poquito"),
                array("value" => 'huntalo_sin_miedo', "label" => "Húntalo sin miedo"),
            ),
        ),
        'sabores' => array(
            'question' => 'Si solo pudieras seguir probando tres de estos sabores por el resto de tu vida,¿Cuales escoges?',
            'selection_limit' => 3,
            'options' => array(
                array("value" => 'yogurt', "label" => "Yogurt"),
                array("value" => 'nuts', "label" => "Frutos Secos"),
                array("value" => 'caramelo', "label" => "Caramelo"),
                array("value" => 'vanilla', "label" => "Vanilla"),
                array("value" => 'pimienta', "label" => "Pimienta"),
                array("value" => 'canela', "label" => "Canela"),
                array("value" => 'balsamic_vinegar', "label" => "Vinagre Balsamico"),
                array("value" => 'miel', "label" => "Miel"),
                array("value" => 'mermelada', "label" => "Mermelada"),
            ),
        )
    );
    
    public $wizard_responses;
    public $user_id;
    public $email;
    public $first_name;
    public $last_name;
    public $password;
    public $current_step;
    public $errors;

    public static function tableName() {
        global $wpdb;
        return $wpdb->prefix . 'taste_wizard_user_responses';
    }

    public static function fetchUserWizard($user_id) {
        global $wpdb;
        $responses_table_name = TasteWizardModel::tableName();
        $query =  $wpdb->prepare("SELECT * FROM $responses_table_name WHERE user_id = %d", $user_id);
        $results = $wpdb->get_results($query);
        
        $responses = array();
        
        foreach($results as $page) {
            if(!array_key_exists($page->question_key, $responses)) {
                $responses = array_merge(
                    $responses,
                    array($page->question_key => array("question_answers" => array()))
                );
            }

            $responses[$page->question_key]["question_answers"] = array_merge(
                $responses[$page->question_key]["question_answers"],
                array($page->question_answer => array("order" => $page->answer_order))
            );
        }

        $taste_wizard = new TasteWizardModel(array("data" => array()), $user_id);
        $taste_wizard->wizard_responses = array_merge($taste_wizard->wizard_responses, $responses);
        return $taste_wizard;
    }

    public function __construct($data, $user_id=null) {
        $this->cleanData($data["data"]);
        $this->errors = new WP_Error();
        $this->user_id = $user_id;
    }

    public function save() {
        global $wpdb;
        $this->validate();
        
        if (!$this->errors->has_errors()) {
            $wpdb->query('START TRANSACTION');
            
            if ($this->user_id) {
                if ($this->deleteUserAnswers() === false) {
                    $this->errors->add(
                        'wizard_responses_save_fail',
                        sprintf(
                            /* translators: %s: Admin email address. */
                            __( '<strong>Error:</strong> Could not register your answers&hellip; please contact the <a href="mailto:%s">site admin</a>!' ),
                            get_option( 'admin_email' )
                        )
                    );
                }
            } else {
                $this->user_id = $this->saveUser();
                
                if (!$this->user_id || is_wp_error($this->user_id)) {
                    $this->errors->add(
                        'registerfail',
                        sprintf(
                            /* translators: %s: Admin email address. */
                            __( '<strong>Error:</strong> Could not register you&hellip; please contact the <a href="mailto:%s">site admin</a>!' ),
                            get_option( 'admin_email' )
                        )
                    );
                } elseif (!$this->saveUserName()) {
                    $this->errors->add(
                        'registerfail',
                        sprintf(
                            /* translators: %s: Admin email address. */
                            __( '<strong>Error:</strong> Could not register you&hellip; please contact the <a href="mailto:%s">site admin</a>!' ),
                            get_option( 'admin_email' )
                        )
                    );
    
                }
            }
            
            if(!$this->errors->has_errors() && !$this->saveResponses()) {
                $this->errors->add(
                    'wizard_responses_save_fail',
                    sprintf(
                        /* translators: %s: Admin email address. */
                        __( '<strong>Error:</strong> Could not register your answers&hellip; please contact the <a href="mailto:%s">site admin</a>!' ),
                        get_option( 'admin_email' )
                    )
                );
            }

            if($this->errors->has_errors()) {
                $wpdb->query('ROLLBACK');
            } else {
                $wpdb->query('COMMIT');
            }
        }
        
    }

    private function deleteUserAnswers() {
        global $wpdb;
        $responses_table_name = TasteWizardModel::tableName();
        return $wpdb->delete($responses_table_name,  ['user_id' =>  $this->user_id], ['%d']);
    }
    
    private function saveUser() {
        global $wpdb;
        $responses_table_name = TasteWizardModel::tableName();

        # $random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );
        $user_name = $this->email;
        $form_id = 7966;
        $user_id = wp_create_user( $user_name, $this->password, $this->email );
        
        if ($user_id) {
            do_action( 'user_registration_after_register_user_action', array(), $form_id, $user_id );
            $template_id = ur_get_single_post_meta( $form_id, 'user_registration_select_email_template' );
            $name_value  = ur_get_user_extra_fields( $user_id );
			UR_Emailer::send_mail_to_user( $this->email, $this->email, $user_id, '', $name_value, array(), $template_id );
        }
        
        return $user_id;
    }

    private function saveUserName() {
        $result_first_name = update_user_meta($this->user_id, 'first_name', $this->first_name);
        if (!$result_first_name) {
            return false;
        }

        $result_last_name = update_user_meta($this->user_id, 'last_name', $this->last_name);
        if (!$result_last_name) {
            return false;
        }


        return true;
    }

    private function saveResponses() {
        global $wpdb;
        $responses_table_name = $wpdb->prefix . 'taste_wizard_user_responses';

        $values = array();
        $current_time = current_time('mysql');
        
        foreach ( $this->wizard_responses as $question_key => $response ) {
            foreach($response["question_answers"] as $question_answer => $question_answer_info) {
                $values[] = $wpdb->prepare(
                    "(%d, %s, %s, %d, %s)",
                    $this->user_id,
                    $question_key,
                    $question_answer,
                    $question_answer_info["order"],
                    $current_time
                );
            }   
        }

        $query = "INSERT INTO $responses_table_name (user_id, question_key, question_answer, answer_order, time) VALUES ";
        $query .= implode( ",\n", $values );
        return $wpdb->query($query);
    }

    private function validate() {
        foreach(TasteWizardModel::$stepsDefintion as $question_key => $definition) {
            if(!array_key_exists($question_key, $this->wizard_responses)) {
                $this->errors->add(
                    'missing_question',
                    __( "<strong>Error:</strong>  Missing question" )
                );
                return; 
            }

            $response = $this->wizard_responses[$question_key];

            if (count($response["question_answers"]) < $definition["selection_limit"]) {
                $this->errors->add(
                    'missing_question_anwsers',
                    __( "<strong>Error:</strong>  Missing question answers" )
                );
                return; 
            }

            foreach($response["question_answers"] as $question_answer => $question_answer_info) {
                if(array_search($question_answer, array_column($definition["options"], "value")) === false) {
                    $this->errors->add(
                        'invalid_taste_option',
                        __( "<strong>Error:</strong> {$question_key} Invalid Taste Option {$question_answer}" )
                    );
                    return; 
                }
            }
        }

        if (!$this->user_id) {
            if($this->password == '') {
                $this->errors->add(
                    'empty_password',
                    __( '<strong>Error:</strong> Please type your password.' )
                );
                
                return;
            }

            if($this->email == '') {
                $this->errors->add(
                    'empty_email',
                    __( '<strong>Error:</strong> Please type your email address.' )
                );
                
                return;
            }
            
            if (!is_email($this->email)) {
                $this->errors->add(
                    'invalid_email',
                    __( '<strong>Error:</strong> The email address is not correct.' )
                );
                
                return;
            }
    
            if(email_exists($this->email)) {
                $this->errors->add(
                    'email_exists',
                    __( '<strong>Error:</strong> This email address is already registered.' )
                );
                
                return;
            }
    
        }
        return;
    }

    private function cleanData($data) {    
        $clean_responses = array();    
        
        foreach(TasteWizardModel::$stepsDefintion as $question_key => $definition) {
            $clean_responses = array_merge($clean_responses,  array($question_key => array("question_answers" => array())));
            
            if(array_key_exists($question_key, $data) && array_key_exists('question_answers', $data[$question_key])) {
                foreach($data[$question_key]['question_answers'] as $question_answer) {
                    if (array_key_exists($question_answer, $data[$question_key])) {
                        $clean_responses[$question_key]["question_answers"] = array_merge(
                            $clean_responses[$question_key]["question_answers"],
                            array(
                                $question_answer => array(
                                    "order" => sanitize_textarea_field($data[$question_key][$question_answer]["order"]),
                                )
                            )
                        );
                    }
                    
                }
            }
        }

        $this->wizard_responses = $clean_responses;
        $this->email = array_key_exists('email', $data) ? sanitize_textarea_field($data['email']) : "";
        $this->first_name = array_key_exists('first_name', $data) ? sanitize_textarea_field($data['first_name']) : "";
        $this->last_name = array_key_exists('last_name', $data) ? sanitize_textarea_field($data['last_name']) : "";
        $this->password = array_key_exists('password', $data) ? sanitize_textarea_field($data['password']) : "";
        $this->current_step = array_key_exists('current_step', $data) ? sanitize_textarea_field($data['current_step']) : 0;
    }
}