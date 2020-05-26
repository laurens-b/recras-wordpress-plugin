registerBlockType('recras/onlinebooking', {
    title: __('Online booking', TEXT_DOMAIN),
    icon: 'admin-site',
    category: 'recras',
    example: {
        attributes: {
            autoresize: false,
            id: null,
            redirect: '',
            package_list: [],
            show_times: true,
            use_new_library: true,
            prefill_enabled: false,
            prefill_date: null,
            prefill_time: null,
            product_amounts: {},
        },
    },

    attributes: {
        autoresize: recrasHelper.typeBoolean(true),
        id: recrasHelper.typeString(),
        redirect: recrasHelper.typeString(),
        package_list: recrasHelper.typeString(), // stored as JSON string
        show_times: recrasHelper.typeBoolean(false),
        use_new_library: recrasHelper.typeBoolean(false),
        prefill_enabled: recrasHelper.typeBoolean(false),
        prefill_date: recrasHelper.typeString(),
        prefill_time: recrasHelper.typeString(),
        product_amounts: recrasHelper.typeString(), // stored as JSON string
    },

    edit: withSelect((select) => {
        return {
            packages: select('recras/store').fetchPackages(false, true),
            pagesPosts: select('recras/store').fetchPagesPosts(),
        }
    })(props => {
        if (!recrasOptions.subdomain) {
            return recrasHelper.elementNoRecrasName();
        }

        const {
            id,
            use_new_library,
            redirect,
            show_times,
            autoresize,
            prefill_date,
            prefill_time,
        } = props.attributes;
        const {
            packages,
            pagesPosts,
        } = props;

        let packagesMapped = Object.values(packages);
        packagesMapped = packagesMapped.filter(p => p.mag_online);
        let packagesWithoutEmpty = JSON.parse(JSON.stringify(packagesMapped));
        // Add empty value as first option, since package is not required
        packagesMapped.unshift({
            id: 0,
            arrangement: '',
        });
        packagesMapped = packagesMapped.map(mapPackage);
        packagesWithoutEmpty = packagesWithoutEmpty.map(mapPackage);

        let package_list;
        try {
            package_list = JSON.parse(props.attributes.package_list);
        } catch (e) {
            package_list = [];
        }

        let product_amounts;
        try {
            product_amounts = JSON.parse(props.attributes.product_amounts);
        } catch (e) {
            product_amounts = {};
        }
        let prefill_enabled = props.attributes.prefill_enabled || Object.keys(product_amounts).length > 0;

        let retval = [];
        const optionsNewLibraryControl = {
            selected: use_new_library ? 'jslibrary' : 'iframe',
            options: [
                {
                    label: __('Seamless (recommended)', TEXT_DOMAIN),
                    value: 'jslibrary',
                },
                {
                    label: __('iframe (uses setting in your Recras)', TEXT_DOMAIN),
                    value: 'iframe',
                },
            ],
            onChange: function(newVal) {
                const useJSLibrary = (newVal === 'jslibrary');
                props.setAttributes({
                    use_new_library: useJSLibrary,
                });
                if (useJSLibrary) {
                    props.setAttributes({
                        id: undefined,
                    });
                } else {
                    props.setAttributes({
                        package_list: undefined,
                    });
                }
            },
            label: __('Integration method', TEXT_DOMAIN),
        };
        let optionsShowTimesControl;
        let optionsPreFillAmountsControl;
        let optionsPreFillDateControl;
        let optionsPreFillTimeControl;
        let preFillControls = [];
        let optionsRedirectControl;
        let optionsAutoresizeControl;
        let optionsPackageControl;
        let packageControls = [];

        if (use_new_library) {
            for (let pck of packagesWithoutEmpty) {
                let ctrl = {
                    checked: package_list.includes(pck.value),
                    className: 'packageList',
                    value: pck.value,
                    label: pck.label,
                    onChange: function(newVal) {
                        if (newVal) {
                            package_list.push(pck.value);
                        } else {
                            package_list.splice(package_list.indexOf(pck.value), 1);
                        }
                        if (package_list.length !== 1) {
                            props.setAttributes({
                                prefill_enabled: false,
                                prefill_date: null,
                                prefill_time: null,
                            });
                        }

                        props.setAttributes({
                            package_list: JSON.stringify(package_list),
                        });
                    },
                };
                packageControls.push(ctrl);
            }
            optionsShowTimesControl = {
                checked: show_times,
                onChange: function(newVal) {
                    props.setAttributes({
                        show_times: newVal,
                    });
                },
                label: __('Preview times in programme', TEXT_DOMAIN),
            };
            optionsPreFillAmountsControl = {
                checked: prefill_enabled,
                onChange: function(newVal) {
                    if (package_list.length !== 1) {
                        newVal = false;
                    }
                    props.setAttributes({
                        prefill_enabled: newVal,
                    });
                },
                disabled: package_list.length !== 1, // This doesn't work. We mimic it using `newVal = false` above
                label: __('Pre-fill amounts (requires exactly 1 package selected)', TEXT_DOMAIN),
            };
            optionsPreFillDateControl = {
                locale: dateSettings.l10n.locale,
                value: prefill_date,
                onChange: function(newVal) {
                    if (package_list.length === 1) {
                        let dateVal = new Date(newVal);
                        dateVal.setHours(12); // Time on newVal is 00:00:00, toISOString converts this to UTC which causes an off-by-one error
                        newVal = dateVal.toISOString().substr(0, 10);
                    } else {
                        newVal = null;
                    }
                    props.setAttributes({
                        prefill_date: newVal,
                    });
                },
                currentDate: prefill_date,
                disabled: package_list.length !== 1, // This doesn't work. We mimic it using `newVal = null` above
            };
            optionsPreFillTimeControl = {
                value: prefill_time,
                onChange: function(newVal) {
                    if (package_list.length !== 1) {
                        newVal = null;
                    }
                    props.setAttributes({
                        prefill_time: newVal,
                    });
                },
                disabled: package_list.length !== 1, // This doesn't work. We mimic it using `newVal = null` above
                label: __('Pre-fill time (requires exactly 1 package selected)', TEXT_DOMAIN),
                help: __('i.e. 14:00', TEXT_DOMAIN),
            };
            optionsRedirectControl = {
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

            if (prefill_enabled && package_list.length === 1 && packages[package_list[0]]) {
                const selectedPackage = packages[package_list[0]];
                let linesNoBookingSize = selectedPackage.regels.filter(function(line) {
                    return line.onlineboeking_aantalbepalingsmethode !== 'boekingsgrootte';
                });
                let linesBookingSize = selectedPackage.regels.filter(function(line) {
                    return line.onlineboeking_aantalbepalingsmethode === 'boekingsgrootte';
                });
                if (linesBookingSize.length > 0) {
                    preFillControls.push({
                        value: product_amounts.bookingsize,
                        onChange: function(newVal) {
                            product_amounts.bookingsize = newVal;

                            props.setAttributes({
                                product_amounts: JSON.stringify(product_amounts)
                            });
                        },
                        label: selectedPackage.weergavenaam || selectedPackage.arrangement,
                        type: 'number',
                        min: 0,
                    });
                }
                linesNoBookingSize.forEach(line => {
                    let ctrl = {
                        value: product_amounts[line.id],
                        onChange: function(newVal) {
                            product_amounts[line.id] = newVal;

                            props.setAttributes({
                                product_amounts: JSON.stringify(product_amounts)
                            });
                        },
                        label: line.beschrijving_templated,
                        type: 'number',
                        min: 0,
                    };
                    preFillControls.push(ctrl);
                });
            }
        } else {
            optionsPackageControl = {
                value: id,
                onChange: function(newVal) {
                    props.setAttributes({
                        id: newVal,
                    });
                },
                options: packagesMapped,
                placeholder: __('Pre-filled package', TEXT_DOMAIN),
                label: __('Pre-filled package (optional)', TEXT_DOMAIN),
            };
            optionsAutoresizeControl = {
                checked: autoresize,
                onChange: function(newVal) {
                    props.setAttributes({
                        autoresize: newVal,
                    });
                },
                label: __('Auto resize iframe', TEXT_DOMAIN),
            };
        }

        retval.push(recrasHelper.elementText('Recras - ' + __('Online booking', TEXT_DOMAIN)));
        retval.push(createEl(RadioControl, optionsNewLibraryControl));
        retval.push(recrasHelper.elementInfo(
            __('Seamless integration uses the styling of your website. At Recras → Settings in the menu on the left, you can set an optional theme.', TEXT_DOMAIN) + '<br>' +
            __('iframe integration uses the styling set in your Recras. You can change the styling in Recras via Settings → Other settings → Custom CSS.', TEXT_DOMAIN)
        ));
        if (use_new_library) {
            retval.push(recrasHelper.elementLabel(__('Package selection', TEXT_DOMAIN)));
            for (let ctrl of packageControls) {
                retval.push(createEl(ToggleControl, ctrl));
            }
            retval.push(recrasHelper.elementInfo(
                __('If you are not seeing certain packages, make sure in Recras "May be presented on a website (via API)" is enabled on the tab "Extra settings" of the package.', TEXT_DOMAIN) + '<br>' +
                __('If you select a single package, it will be pre-filled and will skip the package selection step.', TEXT_DOMAIN)
            ));
            retval.push(createEl(ToggleControl, optionsShowTimesControl));
            retval.push(createEl(ToggleControl, optionsPreFillAmountsControl));
            if (preFillControls.length) {
                preFillControls.forEach(ctrl => {
                    retval.push(createEl(TextControl, ctrl));
                });
            }
            retval.push(recrasHelper.DatePickerControl(
                __('Pre-fill date (requires exactly 1 package selected)', TEXT_DOMAIN),
                optionsPreFillDateControl
            ));
            retval.push(createEl(TextControl, optionsPreFillTimeControl));
            retval.push(createEl(SelectControl, optionsRedirectControl));
        } else {
            retval.push(createEl(SelectControl, optionsPackageControl));
            retval.push(recrasHelper.elementInfo(
                __('If you are not seeing certain packages, make sure in Recras "May be presented on a website (via API)" is enabled on the tab "Extra settings" of the package.', TEXT_DOMAIN)
            ));
            retval.push(createEl(ToggleControl, optionsAutoresizeControl));
        }
        return retval;
    }),

    save: function(props) {
    },
});
