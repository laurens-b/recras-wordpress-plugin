registerGutenbergBlock('recras/package', {
    title: wp.i18n.__('Package', TEXT_DOMAIN),
    icon: 'clipboard',
    category: 'recras',
    example: {
        attributes: {
            id: null,
            show: 'title',
            starttime: '12:00',
            showheader: true,
        },
    },

    attributes: {
        id: recrasHelper.typeString(),
        show: recrasHelper.typeString('title'),
        starttime: recrasHelper.typeString('00:00'),
        showheader: recrasHelper.typeBoolean(true),
    },

    edit: withSelect((select) => {
        return {
            packages: select('recras/store').fetchPackages(true, false),
        }
    })(props => {
        if (!recrasOptions.subdomain) {
            return recrasHelper.elementNoRecrasName();
        }

        const {
            id,
            show,
            showheader,
            starttime,
        } = props.attributes;
        const {
            packages,
        } = props;

        let retval = [];
        const optionsIDControl = {
            value: id,
            onChange: function(newVal) {
                recrasHelper.lockSave('packageID', !newVal);
                props.setAttributes({
                    id: newVal,
                });
            },
            options: packages,
            label: wp.i18n.__('Package', TEXT_DOMAIN),
        };
        if (packages.length === 1) {
            props.setAttributes({
                id: packages[0].value,
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
                    value: 'description',
                    label: wp.i18n.__('Description', TEXT_DOMAIN),
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
                    value: 'persons',
                    label: wp.i18n.__('Minimum number of persons', TEXT_DOMAIN),
                },
                {
                    value: 'price_pp_excl_vat',
                    label: wp.i18n.__('Price p.p. excl. VAT', TEXT_DOMAIN),
                },
                {
                    value: 'price_pp_incl_vat',
                    label: wp.i18n.__('Price p.p. incl. VAT', TEXT_DOMAIN),
                },
                {
                    value: 'programme',
                    label: wp.i18n.__('Programme', TEXT_DOMAIN),
                },
                {
                    value: 'location',
                    label: wp.i18n.__('Starting location', TEXT_DOMAIN),
                },
                {
                    value: 'title',
                    label: wp.i18n.__('Title', TEXT_DOMAIN),
                },
                {
                    value: 'price_total_excl_vat',
                    label: wp.i18n.__('Total price excl. VAT', TEXT_DOMAIN),
                },
                {
                    value: 'price_total_incl_vat',
                    label: wp.i18n.__('Total price incl. VAT', TEXT_DOMAIN),
                },
                {
                    value: 'image_url',
                    label: wp.i18n.__('Relative image URL', TEXT_DOMAIN),
                },
            ],
            label: wp.i18n.__('Property to show', TEXT_DOMAIN),
        };
        let optionsStartTimeControl;
        let optionsShowHeaderControl;

        if (show === 'programme') {
            optionsStartTimeControl = {
                value: starttime,
                onChange: function(newVal) {
                    props.setAttributes({
                        starttime: newVal,
                    });
                },
                placeholder: wp.i18n.__('hh:mm', TEXT_DOMAIN),
                label: wp.i18n.__('Start time', TEXT_DOMAIN),
            };
            optionsShowHeaderControl = {
                checked: showheader,
                onChange: function(newVal) {
                    props.setAttributes({
                        showheader: newVal,
                    });
                },
                label: wp.i18n.__('Show header?', TEXT_DOMAIN),
            };
        }

        retval.push(recrasHelper.elementText('Recras - ' + wp.i18n.__('Package', TEXT_DOMAIN)));

        retval.push(createEl(compSelectControl, optionsIDControl));
        retval.push(recrasHelper.elementInfo(wp.i18n.__('If you are not seeing certain packages, make sure in Recras "May be presented on a website (via API)" is enabled on the tab "Extra settings" of the package.', TEXT_DOMAIN)));
        retval.push(createEl(compSelectControl, optionsShowWhatControl));
        if (show === 'programme') {
            retval.push(createEl(compTextControl, optionsStartTimeControl));
            retval.push(createEl(compToggleControl, optionsShowHeaderControl));

        }
        return retval;
    }),

    save: recrasHelper.serverSideRender,
});
