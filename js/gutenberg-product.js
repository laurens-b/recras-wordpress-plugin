registerGutenbergBlock('recras/product', {
    title: wp.i18n.__('Product', TEXT_DOMAIN),
    icon: 'cart',
    category: 'recras',
    example: {
        attributes: {
            id: null,
            show: 'title',
        },
    },

    attributes: {
        id: recrasHelper.typeString(),
        show: recrasHelper.typeString('title'),
    },

    edit: withSelect((select) => {
        return {
            products: select('recras/store').fetchProducts(),
        }
    })(props => {
        if (!recrasOptions.subdomain) {
            return recrasHelper.elementNoRecrasName();
        }

        const {
            id,
            show,
        } = props.attributes;
        let {
            products,
        } = props;
        if (!Array.isArray(products)) {
            products = [];
        }

        let optionsIDControl;
        if (products.length > 0) {
            optionsIDControl = {
                value: id,
                onChange: function(newVal) {
                    recrasHelper.lockSave('productID', !newVal);
                    props.setAttributes({
                        id: newVal,
                    });
                },
                options: products,
                label: wp.i18n.__('Product', TEXT_DOMAIN),
            };
            if (products.length === 1) {
                props.setAttributes({
                    id: products[0].value,
                });
            }
        }

        let retval = [];
        const optionsShowWhatControl = {
            value: show,
            onChange: function(newVal) {
                props.setAttributes({
                    show: newVal
                });
            },
            options: [
                {
                    value: 'description_long',
                    label: wp.i18n.__('Description (long)', TEXT_DOMAIN),
                },
                {
                    value: 'description',
                    label: wp.i18n.__('Description (short)', TEXT_DOMAIN),
                },
                {
                    value: 'duration',
                    label: wp.i18n.__('Duration', TEXT_DOMAIN),
                },
                {
                    value: 'image_tag',
                    label: wp.i18n.__('Image tag', TEXT_DOMAIN),
                },
                {
                    value: 'image_url',
                    label: wp.i18n.__('Image URL', TEXT_DOMAIN),
                },
                {
                    value: 'minimum_amount',
                    label: wp.i18n.__('Minimum amount', TEXT_DOMAIN),
                },
                {
                    value: 'price_incl_vat',
                    label: wp.i18n.__('Price (incl. VAT)', TEXT_DOMAIN),
                },
                {
                    value: 'title',
                    label: wp.i18n.__('Title', TEXT_DOMAIN),
                },
            ],
            label: wp.i18n.__('Property to show', TEXT_DOMAIN),
        };

        retval.push(recrasHelper.elementText('Recras - ' + wp.i18n.__('Product', TEXT_DOMAIN)));

        if (optionsIDControl) {
            retval.push(createEl(compSelectControl, optionsIDControl));
            retval.push(recrasHelper.elementInfo(wp.i18n.__('If you are not seeing certain products, make sure in Recras "May be presented on a website (via API)" is enabled on the tab "Presentation" of the product.', TEXT_DOMAIN)));
            retval.push(createEl(compSelectControl, optionsShowWhatControl));
        } else {
            retval.push(recrasHelper.elementInfo(wp.i18n.__('Could not find any products. Make sure in Recras "May be presented on a website (via API)" is enabled on the tab "Presentation" of the product.', TEXT_DOMAIN)));
        }
        return retval;
    }),

    save: recrasHelper.serverSideRender,
});
