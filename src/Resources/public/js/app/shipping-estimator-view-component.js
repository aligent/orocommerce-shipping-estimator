/* jshint browser: true */
/* jshint devel: true */
define(function(require) {
    "use strict";

    const ViewComponent = require("oroui/js/app/components/view-component");
    const routing = require("routing");
    const NumberFormatter = require('orolocale/js/formatter/number');
    const LoadingMaskView = require('oroui/js/app/views/loading-mask-view');
    const mediator = require('oroui/js/mediator');
    const $ = require("jquery");
    const _ = require("underscore");

    const messages = {
        shippingEstimatorError: _.__('aligent.shipping.estimator.error.shipping_estimator')
    };

    const ShippingEstimatorViewComponent = ViewComponent.extend({
        template: require("tpl-loader!aligentshippingestimator/js/templates/shipping-estimator.html"),

        /**
         * @inheritDoc
         */
        constructor: function ShippingEstimatorViewComponent() {
            ShippingEstimatorViewComponent.__super__.constructor.apply(this, arguments);
        },

        /**
         * @param {Object} options
         */
        initialize: function(options) {
            this.$el = options._sourceElement;
            this.$shippingEstimatorOutput = $("[data-js='shipping-estimate-output']");
            this.loadingMaskView = new LoadingMaskView({container: this.$el});

            ShippingEstimatorViewComponent.__super__.initialize.apply(this, arguments);

            this.$el.on("submit", _.bind(this._onFormSubmit, this));
        },

        /**
         * Send ajax response to template and render on page
         *
         * @param {Object} response
         */
        render: function(response) {
            const shippingEstimateHtml = this.template(_.extend(response, {
                routing: routing,
                formatter: NumberFormatter,
            }));
            this.$shippingEstimatorOutput.html(shippingEstimateHtml);

            return this;
        },

        /**
         * Submit shipping estimate request to backend and on success populate result into output container
         *
         * @param {Event} e
         */
        _onFormSubmit: function(e) {
            e.preventDefault();
            var $form = $(e.target);

            $.ajax({
                method: "POST",
                url: routing.generate("aligent_shipping_estimate"),
                data: $form.serialize(),
                dataType: "json",
                beforeSend: function() {
                    this.loadingMaskView.show();
                }.bind(this),
                success: function(response) {

                    /* On success the response data looks like this:
                        {  shippingEstimates: [
                            {   "label" : "Shipping method 1 description",
                                "price_value" : 12.3456,
                                "price_currency" : "AUD"
                            },
                            {   "label" : "Shipping method 2 description",
                                "price_value" : 99.999
                                "price_currency" : "NZD"
                            },

                        ]}
                     */

                    this.render(response);
                }.bind(this),
                complete: function() {
                    this.loadingMaskView.hide();
                }.bind(this),
                error: function(response) {
                    const errorMessage = response?.responseJSON?.error?.title || messages.shippingEstimatorError;
                    mediator.execute('showFlashMessage', 'error', errorMessage);

                    /* On error the response data looks like this:
                        (on a validation error)

                        { "error": {
                            "type": "validation_error",
                            "title": "There were one or more validation errors",
                            "error_details": { "state_abbr": "This value should not be blank." }
                            }
                         }

                        (or on an empty cart)

                        { "error": {
                            "type": "cart_empty_error",
                            "title": "Cannot estimate shipping for empty cart",
                            "error_details": {}
                            }
                         }
                     */
                },
            });
        },
    });

    return ShippingEstimatorViewComponent;
});
