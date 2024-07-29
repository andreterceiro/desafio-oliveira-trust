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

        var multiplier;
        // Jquery ready listener
        // Adding the click listener to the button "convert"
        $(document).ready(function() {
            $("#convert").click(function() {
                const valueToConvert = parseFloat($("#valueToConvert").val());

                if (isNaN(valueToConvert) || valueToConvert < 1000 || valueToConvert > 100000) {
                    alert("Por favor entre com um valor entre 1000 e 100000 para conversão");
                } else {
                    const destinationCurrencyAcronym = getDestinationCurrencyAcronym();
                    try {
                        $.get('<?php echo CONFIG::getStartingConversionApiHttpAddress();?>' + destinationCurrencyAcronym + "-BRL")
                            .fail(function() {
                                alert("Não é possível converter para esta moeda");
                            })
                            .then(function(response) {
                                $("#answer").css("visibility","visible");
                                console.log(response);
                                const destinationCurrencyAcronym = getDestinationCurrencyAcronym()
                                multiplier = response[destinationCurrencyAcronym + "BRL"].bid;
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
         * @returns string
         */
        function getDestinationCurrencyAcronym() {
            return $("#destinationCurrency").find(":selected").val();
        }

        function writeDestinationCurrencyAcronym(destinationCurrencyAcronym) {
            $(".labelDestinationCurrencyAcronym").text(destinationCurrencyAcronym);
        }

        function getValueToConvert() {
            return $("#valueToConvert").val();
        }

        function writeValueToConvert(value) {
            $(".labelValueToConvert").text(value);
        }

        function getPaymentMethodLabel() {
            return $("#paymentMethod").find(":selected")[0].label;
        }

        function getPaymentMethodValue() {
            return $("#paymentMethod").find(":selected")[0].value;
        }

        function writePaymentMethod() {
            $(".labelPaymentMethod").text(getPaymentMethodLabel());
        }

        function writeConversionRate(conversionRate) {
            $("#conversionRate").text(conversionRate);
        }

        function calculatePaymentTax(referenceValue) {
            const paymentMethod = getPaymentMethodValue();
            if (paymentMethod == "bankSlip") {
                return referenceValue * .0145;
            } else if (paymentMethod == "creditCard") {
                return referenceValue * .0763;
            }
            throw new Error("Este meio de pagamento não existe");
        }

        function writePaymentTax(paymentTax) {
            $("#paymentTax").text(paymentTax)
        }

        function calculateConversionTax(referenceValue) {
            if (referenceValue < 3000) {
                return referenceValue * .02;
            }
            // Adopting this default value to 3000 too
            return referenceValue * .01;
        }

        function writeConversionTax(conversionTax) {
            $("#conversionTax").text(conversionTax);
        }

        function getValueToConvertMinusDiscounts(valueToConvert, paymentTax, conversionTax) {
            return valueToConvert - paymentTax - conversionTax;
        }

        function writeValueToConvertMinusDiscounts(value) {
            $("#valueToConvertMinusDiscounts").text(value);
        }

        function writeValueConverted(value) {
            $("#valueConverted").text(value);
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
            /*visibility: hidden;*/
        }
        .line-report {
            /*display: block*/
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

                    
</body>
</html>