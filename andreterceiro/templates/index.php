<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script
  src="https://code.jquery.com/jquery-1.12.4.min.js"
  integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
  crossorigin="anonymous"></script>
    <title>conversions</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
    <script>
        /**
         * Get the currencies to populate the combobox (select tag)
         * 
         * @returns {object}
         */
        function getCurrencies() {
            return $.ajax({
                'url': "<?php echo Config::LIST_CURRENCIES_HTTP_ADDRESS; ?>",
                "datatype": "xml"
            }).then(function(XMLcurrencies) {
                return XMLcurrencies.getElementsByTagName("xml")[0].childNodes;
            });
        }

        // Variable to convert the monetary values
        const realsMoneyConversor = new Intl.NumberFormat(
            "pt-BR",
            {
                'style': 'currency',
                'currency': 'BRL'
            }
        );

        const dollarsMoneyConversor = new Intl.NumberFormat(
            "en-US",
            {
                'style': 'currency',
                'currency': 'USD'
            }
        );



        // Invoking the getCurrencies() function and populating the destination currencies combobox
        // (select tag) 
        let currencies; 
        getCurrencies().then(function(ret) { 
            currencies = ret
            for (const currency of currencies) {
                if (currency.tagName != "BRL") {
                    const tagToInsert = `<option value='${currency.tagName}'>${currency.childNodes[0].nodeValue}</option>`;
                    $("#destinationCurrency").append(tagToInsert);
                }
            }
        });

        // Jquery ready listener
        // Adding the click listener to the button "convert"
        $(document).ready(function() {
            $("#convert").click(function() {
                const valueToConvert = parseFloat($("#valueToConvert").val());

                if (isNaN(valueToConvert) || valueToConvert < 1000 || valueToConvert > 100000) {
                    $("#answer").css("visibility","hidden");
                    alert("Por favor entre com um valor entre 1000 e 100000 para conversão");
                } else {
                    const destinationCurrencyAcronym = getDestinationCurrencyAcronym();
                    try {
                        $.get('<?php echo CONFIG::getStartingConversionApiHttpAddress();?>' + destinationCurrencyAcronym + "-BRL")
                            .fail(function() {
                                $("#answer").css("visibility","hidden");
                                alert("Não é possível converter para esta moeda");
                            })
                            .then(function(response) {
                                $("#answer").css("visibility","visible");
                                const destinationCurrencyAcronym = getDestinationCurrencyAcronym()
                                const multiplier = response[destinationCurrencyAcronym + "BRL"].bid;
                                writeDestinationCurrencyAcronym(destinationCurrencyAcronym);
                                
                                const valueToConvert = getValueToConvert();
                                writeValueToConvert(
                                    valueToConvert
                                );
                                
                                writePaymentMethod();
                                
                                writeConversionRate(multiplier);
                                
                                const paymentTax = calculatePaymentTax(
                                    parseFloat($("#valueToConvert").val())
                                );
                                writePaymentTax(
                                    paymentTax
                                );

                                const conversionTax = calculateConversionTax(
                                    parseFloat($("#valueToConvert").val())
                                );
                                writeConversionTax(
                                    conversionTax
                                );

                                const valueToConvertMinusDiscounts = getValueToConvertMinusDiscounts(
                                    valueToConvert, 
                                    paymentTax, 
                                    conversionTax
                                );
                                writeValueToConvertMinusDiscounts(
                                    valueToConvertMinusDiscounts
                                );

                                writeValueConverted(valueToConvertMinusDiscounts / multiplier)
                            });
                    } catch (e) {
                        alert("Não é possível converter para esta moeda");
                    }
                }
            });
        });

        /**
         * Get the acronym related to the currency selected
         *
         * @returns {string}
         */
        function getDestinationCurrencyAcronym() {
            return $("#destinationCurrency").find(":selected").val();
        }

        /**
         * Writes the destination acronym as the content of the specific HTML tags
         *
         * @returns {null}
         */
        function writeDestinationCurrencyAcronym(destinationCurrencyAcronym) {
            $(".labelDestinationCurrencyAcronym").text(destinationCurrencyAcronym);
        }

        /**
         * Returns the vaue to convert from the text field
         *
         * @returns {string}
         */
        function getValueToConvert() {
            return $("#valueToConvert").val();
        }

        /**
         * Writes the destination acronym as the content of the specific HTML tags
         *
         * @param {string} value Value to be written in the HTML page
         *
         * @returns {null}
         */
        function writeValueToConvert(value) {
            $(".labelValueToConvert").text(realsMoneyConversor.format(value));
        }

        /**
         * Returns the payment method selected in the <select /> input filed
         * Returns the label showed in the HTML page, not the option value
         *
         * @returns {string}
         */
        function getPaymentMethodLabel() {
            return $("#paymentMethod").find(":selected")[0].label;
        }

        /**
         * Returns the payment method selected in the <select /> input filed
         * Returns the option value, not the label showed in the HTML page
         *
         * @returns {string}
         */
        function getPaymentMethodValue() {
            return $("#paymentMethod").find(":selected")[0].value;
        }

        /**
         * Writes the payment method as the content of the specific HTML tags
         *
         * @returns {null}
         */
        function writePaymentMethod() {
            $(".labelPaymentMethod").text(getPaymentMethodLabel());
        }

        /**
         * Writes the conversion rate as the content of the specific HTML tag
         *
         * @param {string} conversionRate Value to be written in the HTML page
         *
         * @returns {null}
         */
        function writeConversionRate(conversionRate) {
            $("#conversionRate").text(conversionRate.replace(".", ","));
        }

        /**
         * Calculates the payment tax
         *
         * @param {string} referenceValue Value to be analyzed and who defines
         *                                what will be returned
         *
         * @returns {string}
         */
        function calculatePaymentTax(referenceValue) {
            const paymentMethod = getPaymentMethodValue();
            if (paymentMethod == "bankSlip") {
                return referenceValue * .0145;
            } else if (paymentMethod == "creditCard") {
                return referenceValue * .0763;
            }
            throw new Error("Este meio de pagamento não existe");
        }

        /**
         * Writes the payment tax as the content of the specific HTML tag
         *
         * @param {string} paymentTax Value to be written in the HTML page
         *
         * @returns {null}
         */
        function writePaymentTax(paymentTax) {
            $("#paymentTax").text(realsMoneyConversor.format(paymentTax));
        }

        /**
         * Calculates the conversion tax
         *
         * @param {number} referenceValue Base value to be compared to the threshold
         *                                who defines the value to be returned
         *
         * @returns {string}
         */
        function calculateConversionTax(referenceValue) {
            if (referenceValue < 3000) {
                return referenceValue * .02;
            }
            // Adopting this default value to 3000 too
            return referenceValue * .01;
        }

        /**
         * Writes the conversion tax as the content of the specific HTML tag
         *
         * @param {string} conversionTax Value to be written in the HTML page
         *
         * @returns {null}
         */
        function writeConversionTax(conversionTax) {
            $("#conversionTax").text(realsMoneyConversor.format(conversionTax));
        }

        /**
         * Returns the value to convert minus the discounts
         *
         * @returns {number}
         */
        function getValueToConvertMinusDiscounts(valueToConvert, paymentTax, conversionTax) {
            return valueToConvert - paymentTax - conversionTax;
        }

        /**
         * Writes the value to convert minus the discounts in the HTML page
         *
         * @returns {null}
         */
        function writeValueToConvertMinusDiscounts(value) {
            $("#valueToConvertMinusDiscounts").text(realsMoneyConversor.format(value));
        }

        /**
         * Writes the value converted in the HTML page
         *
         * @returns {null}
         */
        function writeValueConverted(value) {
            const monetaryValue = realsMoneyConversor.format(value);
            $("#valueConverted").text(monetaryValue.substring(2, monetaryValue.length));
        }
    </script>

    <style>
        #valueToConvert {
            width: 100px;
            height: 22px;
        }
        #answer {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #888;
            visibility: hidden;
        }
        .line-report {
            /*display: block*/
        }

        #row-observations {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #888;
        }

        #observations {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <span class="col-4">
                Moeda destino: <select id="destinationCurrency" name="destinationCurrency"></select>
            </span>
            <span class="col-3">
                Valor (R$): <input type="number" min="1000" max="100000" step="0.01" name="valueToConvert" id="valueToConvert" />
            </span>
            <span class="col-4">
                Forma de pagamento:
                <select name="paymentMethod" id="paymentMethod">
                    <option value="bankSlip">Boleto bancário</option>
                    <option value="creditCard">Cartão de crédito</option>
                </select>
            </span>
             
            <span class="col-1">
                <button id="convert" name="convert">Convert</button>
            </span>
        </div>

        <div class="row" id="answer">
            <div class="row">
                <div class="col-6">
                    <div class="col-12 title-report"><h3>Parâmetros de entrada</h3></div>
                    <div class="col-12 line-report"><b>Moeda de origem:</b> BRL (default)</div>
                    <div class="col-12 line-report"><b>Moeda de destino:</b> <span class="labelDestinationCurrencyAcronym"></span></div>
                    <div class="col-12 line-report"><b>Valor para conversão:</b> <span class="labelValueToConvert"></span></div>
                    <div class="col-12 line-report"><b>Forma de pagamento:</b> <span class="labelPaymentMethod"></span></div>
                </div>
                <div class="col-6">
                    <div class="col-12"><h3>Parâmetros de saída</h3></div>
                    <div class="col-12"><b>Moeda de origem:</b> BRL (default)</div>
                    <div class="col-12"><b>Moeda de destino:</b> <span class="labelDestinationCurrencyAcronym"></span></div>
                    <div class="col-12"><b>Valor para conversão:</b> <span class="labelValueToConvert"></span></div>
                    <div class="col-12"><b>Forma de pagamento:</b> <span class="labelPaymentMethod"></span></div>
                    <div class="col-12"><b>Valor da "Moeda de destino" usado para conversão (taxa):</b> <span id="conversionRate"></span></div>
                    <div class="col-12"><b>Valor comprado em "Moeda de destino": </b> <span id="valueConverted"></span></div>
                    <div class="col-12"><b>Taxa de pagamento:</b> <span id="paymentTax"></span></div>
                    <div class="col-12"><b>Taxa de conversão:</b> <span id="conversionTax"></span></div>
                    <div class="col-12"><b>Valor utilizado para conversão descontando as taxas:</b> <span id="valueToConvertMinusDiscounts"></span></div>
                </div>
            </div>
            <div class="row" id="row-observations">
                <div class="col-12" id="observations">
                    <b>OBS: </b>para se chegar ao valor comprado na moeda destino, <b>divida</b> o valor
                    para conversão já descontadas as taxas.
                </div>
            </div>
        </div>
    </div>
</body>
</html>