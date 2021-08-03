registerGutenbergBlock('recras/bookprocess', {
    title: wp.i18n.__('Book process', TEXT_DOMAIN),
    icon: 'editor-ul',
    category: 'recras',
    example: {
        attributes: {
            id: null,
        },
    },

    attributes: {
        id: recrasHelper.typeString(),
    },

    edit: withSelect((select) => {
        return {
            bookprocesses: select('recras/store').fetchBookprocesses(),
        }
    })(props => {
        if (!recrasOptions.subdomain) {
            return recrasHelper.elementNoRecrasName();
        }

        const {
            id,
        } = props.attributes;
        const {
            bookprocesses,
        } = props;

        let retval = [];
        const optionsIDControl = {
            value: id,
            onChange: function(newVal) {
                recrasHelper.lockSave('bookprocessID', !newVal);
                props.setAttributes({
                    id: newVal,
                });
            },
            options: bookprocesses,
            label: wp.i18n.__('Book process', TEXT_DOMAIN),
        };
        if (bookprocesses.length === 1) {
            props.setAttributes({
                id: bookprocesses[0].value,
            });
        }

        retval.push(recrasHelper.elementText('Recras - ' + wp.i18n.__('Book process', TEXT_DOMAIN)));

        retval.push(createEl(compSelectControl, optionsIDControl));

        return retval;
    }),

    save: recrasHelper.serverSideRender,
});
