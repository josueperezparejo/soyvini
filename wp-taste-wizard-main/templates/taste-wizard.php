<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<?php
foreach($errors as $error) {
?>  
    <div class='woocommerce-error'> <?php echo  $error ?></div>
<?php
}
?>

<div class="taste-wizard-container">
    <form action='./' method='POST' class="taste-wizard-form">

        <?php wp_nonce_field( 'taste_wizard_form_action', 'taste_wizard_wpnounce' ); ?>
        <input type="hidden" name="taste_wizard_form" value="1">
        <input type="hidden" name="data[current_step]" value="<?php echo $taste_wizard->current_step ?>" class="taste-wizard-current-step-input">
        <div class="taste-wizard-step-0" style="display:none">
            <img class="img-quiz-min-vini" src="<?php echo plugins_url("/taste-wizard/img/copa-vino.svg") ?>" alt="Copa de vino Quiz"/>
            <h1>
                Estas muy cerca de descubrir
                tu paladar como nunca antes
            </h1>
            <p>
            Te haremos una serie de preguntas muy fáciles que nos ayudarán a entender mejor que sabores te gustan para así poder recomendarte vinos.     
            </p>
            <span>Terminar el quiz te tomará 5 minutos.</span>
            <input type="button" value="Empieza ahora" class="taste-wizard-step-next-step-0 btn-step">
        </div>

        <?php
            $index = 1;
            foreach(TasteWizardModel::$stepsDefintion as $question_key => $defintion) {
        ?>   
                <div class="taste-wizard-step-<?php echo $index ?> taste-wizard-step" style="display:none">
                    <p> <?php echo $defintion['question']?></p>
                    
                    <div class='taste-wizard-options-container'>
                        <?php foreach($defintion['options'] as $option) {
                            $option_is_selected = array_key_exists($option['value'], $taste_wizard->wizard_responses[$question_key]["question_answers"]);
                            $order = $option_is_selected ? $taste_wizard->wizard_responses[$question_key]["question_answers"][$option["value"]]["order"] : "";
                        ?>
                            <div
                                class="taste-wizard-option"
                                data-value="<?php echo $option['value']?>"
                                data-selected="<?php echo $option_is_selected ? "selected": "" ?>"
                            >
                                <input
                                    type="hidden"
                                    name="data[<?php echo $question_key?>][<?php echo $option['value']?>][order]"
                                    class="taste-wizard-order-input"
                                    value="<?php echo $order?>"
                                />
                                <?php if($defintion['selection_limit'] > 1) { ?>
                                    <span class="taste-wizard-option-number"> <?php echo $order?> </span>
                                <?php } ?>
                                <?php if($index !== 1) { ?>
                                    <div>
                                        <img src="<?php echo plugins_url( "/taste-wizard/img/{$option['value']}.svg", 'taste-wizard' ) ?>" />
                                    </div>
                                <?php } ?>
                                <span class="taste-wizard-option-label"><?php echo $option['label']?></span>
                            </div>
                        <?php }?>
                        <select
                            name='data[<?php echo $question_key?>][question_answers][]'
                            class='taste-wizard-select'
                            style="display:none"
                            <?php echo $defintion['selection_limit'] > 1 ? "multiple" : ""?>
                            data-limit="<?php echo $defintion['selection_limit'] ?>"
                        >
                            <option value=""></option>
                            <?php foreach($defintion['options'] as $option) {
                                $selected = array_key_exists($option['value'], $taste_wizard->wizard_responses[$question_key]["question_answers"]) ? 'selected="selected"': ''
                            ?>
                                <option value="<?php echo $option['value']?>" <?php echo $selected?>>
                                    <?php echo $option['label']?>
                                </option>
                            <?php }?>
                        </select>
                    </div>

                    
                    <div class="taste-wizard-navigator">
                        <button class="taste-wizard-step-previous" type="button">
                            <img src="<?php echo plugins_url( "/taste-wizard/img/arrow_back.svg", 'taste-wizard' ) ?>" />
                        </button>
                        <button class="taste-wizard-step-next" type="button">
                            <img src="<?php echo plugins_url( "/taste-wizard/img/arrow_next.svg", 'taste-wizard' ) ?>" />
                        </button>
                    </div>
                </div>
        <?php
                $index++;
            } 
        ?>

        <div class="taste-wizard-step-<?php echo $index ?>" style="display:none">
        <img class="img-quiz-min-vini" src="<?php echo plugins_url("/taste-wizard/img/vino-final.svg") ?>" alt="Copa de vino Quiz Final">
        <h2>¿Listo para ver tus resultados?</h2>
        <?php if($taste_wizard->user_id) {?>       
            <div class="form-quiz">
                <div class="btn-steps-condicion">
                    <input type="submit" value="Enviar" class="taste-wizard-step-submit btn-step">
                </div>
            </div>
        <?php } else { ?>
            <p>Ingresa tu email para que podamos enviarte tus recomendaciones</p>            
            <div class="form-quiz">
                <input class="input-email-success" name='data[email]' type='email' placeholder="Tu email" value="<?php echo $taste_wizard->email ?>" required/>
                
                <input class="input-email-success" name='data[first_name]' type="text" placeholder="Nombre" value="<?php echo $taste_wizard->first_name ?>" required/>
            
                <input class="input-email-success" name='data[last_name]' type="text" placeholder="Apellido" value="<?php echo $taste_wizard->last_name ?>" required/>

                <input class="input-email-success" name='data[password]' type="password" placeholder="Contraseña" required/>

                <div class="btn-steps-condicion">
                    <input type="submit" value="Enviar" class="taste-wizard-step-submit btn-step">
                </div>
            </div>
        <?php } ?>
    </form>
</div>