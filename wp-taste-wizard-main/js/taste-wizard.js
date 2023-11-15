jQuery(document).ready(($) => {
    class TasteWizard {
        constructor(wizardData = {}) {
            this._loadCurrentStep();
            this.wizardData = wizardData
        }
    
        start() {
            this._initButtons();
            this._initOptions();
            this. _initSelects();
            this._loadCurrentContainer();
            this._showContainer();
            this._initCurrentNextButton();
            this._initForm();
        }

        moveNext() {
            this._hideContainer();
            this._incrementStep();
            this._loadCurrentContainer();
            this._initCurrentNextButton();
            this._showContainer();
            
        }

        movePrevious() {
            this._hideContainer();
            this._decrementStep();
            this._loadCurrentContainer();
            this._showContainer();
        }

        _incrementStep() {
            this.currentStep++;
            this._setCurrentStepInput();
        }

        _decrementStep() {
            this.currentStep--;
            this._setCurrentStepInput();
        }

        _setCurrentStepInput() {
            $(TasteWizard.classes.CURRENT_STEP_CLASS_PREFIX_INPUT).val(this.currentStep);
        }
        
        _loadCurrentStep() {
            this.currentStep = parseInt(
                $(TasteWizard.classes.CURRENT_STEP_CLASS_PREFIX_INPUT).val()
            );
        }

        _loadCurrentContainer() {
            this.$currentStepContainer = $(`.${TasteWizard.classes.STEP_CLASS_PREFIX}${this.currentStep}`);
        }
    

        _hideContainer() {
            this.$currentStepContainer.css('display', 'none');
        }

        _showContainer() {
            this.$currentStepContainer.css('display', '');
        }

        _initSelects() {
            $(TasteWizard.classes.STEP_CLASS_SELECT).each(function() {
                const $select = $(this);
                const currentSelectedValue = $select.val();
                if(currentSelectedValue) {
                    $(`.taste-wizard-option[data-value="${currentSelectedValue}"]`, $select.parent('.taste-wizard-step'))
                        .attr('data-selected', 'selected');
                }
            });
        }

        _initOptions() {
            const $options = $(`.${TasteWizard.classes.STEP_CLASS_OPTION_PREFIX}`);
            const self = this;
            $options.on('click', function() { self._onOptionClick(this) });
        }

        _initButtons() {
            this.$nextButton = $(`.${TasteWizard.classes.STEP_CLASS_NEXT_BUTTON}`);
            this.$prevButton = $(`.${TasteWizard.classes.STEP_CLASS_PREVIOUS_BUTTON}`);

            this.$nextButton.attr('disabled', 'disabled');
            this.$nextButton.on('click', (e) => this.moveNext());
            this.$prevButton.on('click', (e) => this.movePrevious());
            $(TasteWizard.classes.STEP_0_CLASS_PREFIX).on('click', (e) => this.moveNext());
        }

        _initCurrentNextButton() {
            const $select =  $(TasteWizard.classes.STEP_CLASS_SELECT, this.$currentStepContainer);
            this._toggleNextButtonStatus($select);
        }

        _initForm() {
            $(TasteWizard.classes.FORM_CLASS).on('submit', (event) => {
                $(TasteWizard.classes.FORM_SUBMIT_CLASS, event.target)
                    .attr('disabled', 'disabled')
                    .val('Enviando..');
            });
        }

        _onOptionClick(optionElem) {
            const $select = $(TasteWizard.classes.STEP_CLASS_SELECT, this.$currentStepContainer);
            const $selectedOption = $(optionElem);
            const $selectedOptionOrderInput = $(TasteWizard.classes.STEP_CLASS_OPTION_ORDER_INPUT, $selectedOption);
            const optionLimit = parseInt($select.attr('data-limit'), 10);
            
            if($select.attr('multiple')) {
                if($selectedOption.attr('data-selected')) {
                    $selectedOption.removeAttr('data-selected')

                    $(`.${TasteWizard.classes.STEP_CLASS_OPTION_NUMBER}`, $selectedOption).text('');
                    
                    $select.val(
                        $select.val().filter((optionVal) => optionVal !== $selectedOption.attr('data-value'))
                    );
                    $selectedOptionOrderInput.val('');
                    
                    const selectedOptions =  $(
                        `.${TasteWizard.classes.STEP_CLASS_OPTION_PREFIX}[data-selected="selected"]`,
                        this.$currentStepContainer
                    ).get();

                    selectedOptions.sort((selected1, selected2) => (
                        parseInt($(TasteWizard.classes.STEP_CLASS_OPTION_ORDER_INPUT, selected1).val()) -
                        parseInt($(TasteWizard.classes.STEP_CLASS_OPTION_ORDER_INPUT, selected2).val())
                    ));
                    
                    selectedOptions.forEach(($elem, index) => {
                        const $optionNumber = $(`.${TasteWizard.classes.STEP_CLASS_OPTION_NUMBER}`, $elem);
                        const $orderInput = $(TasteWizard.classes.STEP_CLASS_OPTION_ORDER_INPUT, $elem);
                        $optionNumber.text(index +  1);
                        $orderInput.val(index +  1);
                    });
                } else if ($select.val().length < optionLimit) {
                    $select.val([...$select.val(), $selectedOption.attr('data-value')]);
                    $selectedOption.attr({ 'data-selected': 'selected' });
                    const $orderInput = $(TasteWizard.classes.STEP_CLASS_OPTION_ORDER_INPUT, $selectedOption);
                    const $optionNumber = $(`.${TasteWizard.classes.STEP_CLASS_OPTION_NUMBER}`, $selectedOption);
                    $orderInput.val($select.val().length);
                    $optionNumber.text($select.val().length);
                }
            } else {
                $(`.${TasteWizard.classes.STEP_CLASS_OPTION_PREFIX}`, this.$currentStepContainer)
                    .removeAttr('data-selected');
                $selectedOption.attr('data-selected', 'selected');
                $select.val($selectedOption.attr('data-value'));
            }

            const optionSelectedCount = this._optionsSelected($select);
            this._toggleNextButtonStatus($select);
            
            if(optionSelectedCount == optionLimit) {
                this.moveNext();
            }
        }

        _toggleNextButtonStatus($select) {
            const optionLimit = parseInt($select.attr('data-limit'), 10);
            const $nextButon =  $(`.${TasteWizard.classes.STEP_CLASS_NEXT_BUTTON}`, this.$currentStepContainer);
            const optionSelectedCount = this._optionsSelected($select);
            

            if(optionSelectedCount < optionLimit) {
                $nextButon.attr('disabled', 'disabled');
                return;
            }
            

            $nextButon.removeAttr('disabled');
        }

        _optionsSelected($select) {
            let optionSelectedCount;
            
            if($select.attr('multiple')) {
                optionSelectedCount = $select.val().length;
            } else {
                optionSelectedCount = $select.val() == '' ? 0 : 1;
            }

            return optionSelectedCount;
        }
    }

    TasteWizard.classes = {
        STEP_CLASS_PREFIX: 'taste-wizard-step-',
        STEP_CLASS_OPTION_PREFIX: 'taste-wizard-option',
        STEP_CLASS_OPTION_NUMBER: 'taste-wizard-option-number',
        STEP_CLASS_PREVIOUS_BUTTON: 'taste-wizard-step-previous',
        STEP_CLASS_NEXT_BUTTON: 'taste-wizard-step-next',
        STEP_CLASS_SELECT: '.taste-wizard-select',
        STEP_CLASS_OPTION_ORDER_INPUT: '.taste-wizard-order-input',
        STEP_0_CLASS_PREFIX: '.taste-wizard-step-next-step-0',
        CURRENT_STEP_CLASS_PREFIX_INPUT: '.taste-wizard-current-step-input',
        FORM_CLASS: '.taste-wizard-form',
        FORM_SUBMIT_CLASS: '.taste-wizard-step-submit',
    };
    const tasteWizard = new TasteWizard();
    tasteWizard.start();
});