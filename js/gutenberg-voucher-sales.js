registerGutenbergBlock('recras/voucher-sales', {
    title: __('Voucher sales', TEXT_DOMAIN),
    icon: 'money',
    category: 'recras',
    example: {
        attributes: {
            id: null,
            redirect: '',
        },
    },

    attributes: {
        id: recrasHelper.typeString(),
        redirect: recrasHelper.typeString(),
    },

    edit: withSelect((select) => {
        return {
            pagesPosts: select('recras/store').fetchPagesPosts(),
            voucherTemplates: select('recras/store').fetchVoucherTemplates(),
        }
    })(props => {
        if (!recrasOptions.subdomain) {
            return recrasHelper.elementNoRecrasName();
        }

        const {
            id,
            redirect,
        } = props.attributes;
        const {
            pagesPosts,
            voucherTemplates,
        } = props;

        if (pagesPosts === undefined || !pagesPosts.length) {
            return [
                recrasHelper.elementText(__('Loading data...', TEXT_DOMAIN))
            ];
        }

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
            label: __('Voucher template', TEXT_DOMAIN),
        };
        if (voucherTemplates.length === 1) {
            props.setAttributes({
                id: voucherTemplates[0].value,
            });
        }

        const optionsRedirectControl = {
            value: redirect,
            onChange: function(newVal) {
                props.setAttributes({
                    redirect: newVal
                });
            },
            options: pagesPosts,
            placeholder: __('i.e. https://www.recras.com/thanks/', TEXT_DOMAIN),
            label: __('Thank-you page (optional, leave empty to not redirect)', TEXT_DOMAIN),
        };

        retval.push(recrasHelper.elementText('Recras - ' + __('Voucher sales', TEXT_DOMAIN)));
        retval.push(createEl(compSelectControl, optionsIDControl));
        retval.push(createEl(compSelectControl, optionsRedirectControl));

        return retval;
    }),

    save: recrasHelper.serverSideRender,
});
