registerGutenbergBlock('recras/voucher-info', {
    title: wp.i18n.__('Voucher info', TEXT_DOMAIN),
    icon: 'money',
    category: 'recras',
    example: {
        attributes: {
            id: null,
            show: 'name',
        },
    },

    attributes: {
        id: recrasHelper.typeString(),
        show: recrasHelper.typeString('name'),
    },

    edit: withSelect((select) => {
        return {
            voucherTemplates: select('recras/store').fetchVoucherTemplates(),
        }
    })(props => {
        if (!recrasOptions.subdomain) {
            return recrasHelper.elementNoRecrasName();
        }

        const {
            id,
            show,
        } = props.attributes;
        const {
            voucherTemplates,
        } = props;

        let retval = [];
        const optionsIDControl = {
            value: id,
            onChange: function(newVal) {
                recrasHelper.lockSave('voucherID', !newVal);
                props.setAttributes({
                    id: newVal,
                });
            },
            options: voucherTemplates,
            label: wp.i18n.__('Voucher template', TEXT_DOMAIN),
        };
        if (voucherTemplates.length === 1) {
            props.setAttributes({
                id: voucherTemplates[0].value,
            });
        }

        const optionsShowWhatControl = {
            value: show,
            onChange: function(newVal) {
                props.setAttributes({
                    show: newVal
                });
            },
            options: [
                {
                    value: 'name',
                    label: wp.i18n.__('Name', TEXT_DOMAIN),
                },
                {
                    value: 'price',
                    label: wp.i18n.__('Price', TEXT_DOMAIN),
                },
                {
                    value: 'validity',
                    label: wp.i18n.__('Number of days valid', TEXT_DOMAIN),
                },
            ],
            label: wp.i18n.__('Property to show', TEXT_DOMAIN),
        };

        retval.push(recrasHelper.elementText('Recras - ' + wp.i18n.__('Voucher info', TEXT_DOMAIN)));
        retval.push(createEl(compSelectControl, optionsIDControl));
        retval.push(createEl(compSelectControl, optionsShowWhatControl));

        return retval;
    }),

    save: recrasHelper.serverSideRender,
});
