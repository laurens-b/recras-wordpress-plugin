registerBlockType('recras/package', {
    title: __('Package', TEXT_DOMAIN),
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
            label: __('Package', TEXT_DOMAIN),
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
                    label: __('Description', TEXT_DOMAIN),
                },
                {
                    value: 'duration',
                    label: __('Duration', TEXT_DOMAIN),
                },
                {
                    value: 'image_tag',
                    label: __('Image tag', TEXT_DOMAIN),
                },
                {
                    value: 'persons',
                    label: __('Minimum number of persons', TEXT_DOMAIN),
                },
                {
                    value: 'price_pp_excl_vat',
                    label: __('Price p.p. excl. VAT', TEXT_DOMAIN),
                },
                {
                    value: 'price_pp_incl_vat',
                    label: __('Price p.p. incl. VAT', TEXT_DOMAIN),
                },
                {
                    value: 'programme',
                    label: __('Programme', TEXT_DOMAIN),
                },
                {
                    value: 'location',
                    label: __('Starting location', TEXT_DOMAIN),
                },
                {
                    value: 'title',
                    label: __('Title', TEXT_DOMAIN),
                },
                {
                    value: 'price_total_excl_vat',
                    label: __('Total price excl. VAT', TEXT_DOMAIN),
                },
                {
                    value: 'price_total_incl_vat',
                    label: __('Total price incl. VAT', TEXT_DOMAIN),
                },
                {
                    value: 'image_url',
                    label: __('Relative image URL', TEXT_DOMAIN),
                },
            ],
            label: __('Property to show', TEXT_DOMAIN),
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
                placeholder: __('hh:mm', TEXT_DOMAIN),
                label: __('Start time', TEXT_DOMAIN),
            };
            optionsShowHeaderControl = {
                checked: showheader,
                onChange: function(newVal) {
                    props.setAttributes({
                        showheader: newVal,
                    });
                },
                label: __('Show header?', TEXT_DOMAIN),
            };
        }

        retval.push(recrasHelper.elementText('Recras - ' + __('Package', TEXT_DOMAIN)));

        retval.push(createEl(SelectControl, optionsIDControl));
        retval.push(recrasHelper.elementInfo(__('If you are not seeing certain packages, make sure in Recras "May be presented on a website (via API)" is enabled on the tab "Extra settings" of the package.', TEXT_DOMAIN)));
        retval.push(createEl(SelectControl, optionsShowWhatControl));
        if (show === 'programme') {
            retval.push(createEl(TextControl, optionsStartTimeControl));
            retval.push(createEl(ToggleControl, optionsShowHeaderControl));

        }
        return retval;
    }),

    save: recrasHelper.serverSideRender,
});
