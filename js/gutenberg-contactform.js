registerGutenbergBlock('recras/contactform', {
    title: __('Contact form', TEXT_DOMAIN),
    icon: 'email',
    category: 'recras',
    example: {
        attributes: {
            id: null,
            showtitle: false,
            showlabels: true,
            showplaceholders: true,
            arrangement: null,
            element: 'dl',
            single_choice_element: 'select',
            submittext: __('Send', TEXT_DOMAIN),
            redirect: '',
        },
    },

    attributes: {
        id: recrasHelper.typeString(),
        showtitle: recrasHelper.typeBoolean(true),
        showlabels: recrasHelper.typeBoolean(true),
        showplaceholders: recrasHelper.typeBoolean(true),
        arrangement: recrasHelper.typeString(),
        element: recrasHelper.typeString('dl'),
        single_choice_element: recrasHelper.typeString('select'),
        submittext: recrasHelper.typeString(__('Send', TEXT_DOMAIN)),
        redirect: recrasHelper.typeString(),
    },

    edit: withSelect((select) => {
        return {
            contactForms: select('recras/store').fetchContactForms(),
            packages: select('recras/store').fetchPackages(true, true),
            pagesPosts: select('recras/store').fetchPagesPosts(),
        }
    })(props => {
        if (!recrasOptions.subdomain) {
            return recrasHelper.elementNoRecrasName();
        }

        const {
            id,
            showtitle,
            showlabels,
            showplaceholders,
            arrangement,
            element,
            single_choice_element,
            submittext,
            redirect,
        } = props.attributes;
        const {
            contactForms,
            packages,
            pagesPosts,
        } = props;

        let retval = [];
        const optionsIDControl = {
            value: id,
            onChange: function(newVal) {
                recrasHelper.lockSave('contactFormID', !newVal);
                props.setAttributes({
                    id: newVal,
                });
            },
            options: contactForms,
            label: __('Contact form', TEXT_DOMAIN),
        };
        if (contactForms.length === 1) {
            props.setAttributes({
                id: contactForms[0].value,
            });
        }
        const optionsShowTitleControl = {
            checked: showtitle,
            onChange: function(newVal) {
                props.setAttributes({
                    showtitle: newVal,
                });
            },
            label: __('Show title?', TEXT_DOMAIN),
        };
        const optionsShowLabelsControl = {
            checked: showlabels,
            onChange: function(newVal) {
                props.setAttributes({
                    showlabels: newVal,
                });
            },
            label: __('Show labels?', TEXT_DOMAIN),
        };
        const optionsShowPlaceholdersControl = {
            checked: showplaceholders,
            onChange: function(newVal) {
                props.setAttributes({
                    showplaceholders: newVal,
                });
            },
            label: __('Show placeholders?', TEXT_DOMAIN),
        };
        const optionsPackageControl = {
            value: arrangement,
            onChange: function(newVal) {
                props.setAttributes({
                    arrangement: newVal,
                });
            },
            options: packages,
            label: __('Package (optional)', TEXT_DOMAIN),
        };
        const optionsElementControl = {
            value: element,
            onChange: function(newVal) {
                props.setAttributes({
                    element: newVal
                });
            },
            options: [
                {
                    value: 'dl',
                    label: __('Definition list', TEXT_DOMAIN),
                },
                {
                    value: 'ol',
                    label: __('Ordered list', TEXT_DOMAIN),
                },
                {
                    value: 'table',
                    label: __('Table', TEXT_DOMAIN),
                },
            ],
            label: __('HTML element', TEXT_DOMAIN),
        };
        const optionsSingleChoiceControl = {
            value: single_choice_element,
            onChange: function(newVal) {
                props.setAttributes({
                    single_choice_element: newVal
                });
            },
            options: [
                {
                    value: 'select',
                    label: __('Drop-down list (Select)', TEXT_DOMAIN),
                },
                {
                    value: 'radio',
                    label: __('Radio buttons', TEXT_DOMAIN),
                },
            ],
            label: __('Element for single choices', TEXT_DOMAIN),
        };
        const optionsSubmitTextControl = {
            value: submittext,
            onChange: function(newVal) {
                props.setAttributes({
                    submittext: newVal
                });
            },
            placeholder: __('Submit button text', TEXT_DOMAIN),
            label: __('Submit button text', TEXT_DOMAIN),
        };
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
            type: 'url',
        };

        retval.push(recrasHelper.elementText('Recras - ' + __('Contact form', TEXT_DOMAIN)));

        retval.push(createEl(compSelectControl, optionsIDControl));
        retval.push(createEl(compToggleControl, optionsShowTitleControl));
        retval.push(createEl(compToggleControl, optionsShowLabelsControl));
        retval.push(createEl(compToggleControl, optionsShowPlaceholdersControl));
        retval.push(createEl(compSelectControl, optionsPackageControl));

        retval.push(recrasHelper.elementInfo(__('Some packages may not be available for all contact forms. You can change this by editing your contact forms in Recras.', TEXT_DOMAIN)));
        retval.push(recrasHelper.elementInfo(__('If you are still missing packages, make sure in Recras "May be presented on a website (via API)" is enabled on the tab "Extra settings" of the package.', TEXT_DOMAIN)));

        retval.push(createEl(compSelectControl, optionsElementControl));
        retval.push(createEl(compSelectControl, optionsSingleChoiceControl));
        retval.push(createEl(compTextControl, optionsSubmitTextControl));
        retval.push(createEl(compSelectControl, optionsRedirectControl));
        return retval;
    }),

    save: recrasHelper.serverSideRender,
});
