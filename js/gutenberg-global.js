const createEl = wp.element.createElement;
const registerGutenbergBlock = wp.blocks.registerBlockType;
const compDatePicker = wp.components.DatePicker;
const compRadioControl = wp.components.RadioControl;
const compSelectControl = wp.components.SelectControl;
const compTextControl = wp.components.TextControl;
const compToggleControl = wp.components.ToggleControl;

const {
    registerStore,
    withSelect,
} = wp.data;

const dateSettings = wp.date.__experimentalGetSettings();
const TEXT_DOMAIN = 'recras';

const recrasHelper = {
    serverSideRender: () => null,

    DatePickerControl: (label, options) => {
        return createEl(
            'div',
            null,
            recrasHelper.elementLabel(label),
            createEl(compDatePicker, options)
        );
    },
    elementInfo: (text) => {
        // WP 5.5 has createInterpolateElement instead of RawHTML
        // https://make.wordpress.org/core/2020/07/17/introducing-createinterpolateelement/
        return createEl(
            wp.element.RawHTML,
            null,
            '<p class="recrasInfoText">' + text + '</p>'
        );
    },
    elementLabel: (text) => {
        return createEl(
            'label',
            {
                class: 'components-base-control',
            },
            text
        );
    },
    elementNoRecrasName: () => {
        const settingsLink = `<a href="${ recrasOptions.settingsPage }" target="_blank">${ wp.i18n.__('Recras → Settings menu', TEXT_DOMAIN) }</a>`;
        return [
            recrasHelper.elementInfo(wp.i18n.sprintf(wp.i18n.__('Please enter your Recras name in the %s before adding widgets.', TEXT_DOMAIN), settingsLink)),
        ];
    },
    elementText: (text) => {
        return createEl(
            'div',
            null,
            text
        );
    },
    lockSave: (lockName, bool) => {
        if (bool) {
            wp.data.dispatch('core/editor').lockPostSaving(lockName);
        } else {
            wp.data.dispatch('core/editor').unlockPostSaving(lockName);
        }
    },

    typeBoolean: (defVal) => ({
        default: (defVal !== undefined) ? defVal : true,
        type: 'boolean',
    }),
    typeString: (defVal) => ({
        default: (defVal !== undefined) ? defVal : '',
        type: 'string',
    }),
};

const paramsWithPage = function(page) {
    const params = new URLSearchParams({
        page,
        per_page: 100, // WP has a hard limit of 100 posts per page
        orderby: 'title',
        order: 'asc',
        _fields: 'id,title,link', // We're only interested in these fields
    });
    return params.toString();
};
const mapSelect = function(label, value) {
    return {
        label: label,
        value: value,
    };
};
const mapBookprocess = function(idBookprocess) {
    return mapSelect(idBookprocess[1], idBookprocess[0]);
};
const mapContactForm = function(idName) {
    return mapSelect(idName[1], idName[0]);
};
const mapPackage = function(pack) {
    return mapSelect(pack.arrangement, pack.id);
};
const mapPagesPosts = function(pagePost, prefix) {
    // SelectControl does not support optgroups :(
    // https://github.com/WordPress/gutenberg/issues/17032
    return mapSelect(prefix + pagePost.title.rendered, pagePost.link);
};
const mapProduct = function(product) {
    return mapSelect(product.naam, product.id);
};
const mapVoucherTemplate = function(template) {
    return mapSelect(template.name, template.id);
};

const recrasActions = {
    fetchAPI(path) {
        return {
            type: 'FETCH_API',
            path,
        }
    },

    setBookprocesses(bookprocesses) {
        return {
            type: 'SET_BOOKPROCESSES',
            bookprocesses,
        }
    },

    setContactForms(contactForms) {
        return {
            type: 'SET_FORMS',
            contactForms,
        }
    },

    setPackages(packages) {
        return {
            type: 'SET_PACKAGES',
            packages,
        }
    },

    setPagesPosts(pagesPosts) {
        return {
            type: 'SET_PAGES_POSTS',
            pagesPosts,
        }
    },

    setProducts(products) {
        return {
            type: 'SET_PRODUCTS',
            products,
        }
    },

    setVoucherTemplates(voucherTemplates) {
        return {
            type: 'SET_VOUCHERS',
            voucherTemplates,
        }
    },
};
const recrasStore = registerStore('recras/store', {
    reducer(state = {
        bookprocesses: {},
        contactForms: {},
        packages: {},
        pagesPosts: {},
        products: {},
        voucherTemplates: {},
    }, action) {
        switch (action.type) {
            case 'SET_BOOKPROCESSES':
                return {
                    ...state,
                    bookprocesses: action.bookprocesses,
                };
            case 'SET_FORMS':
                return {
                    ...state,
                    contactForms: action.contactForms,
                };
            case 'SET_PACKAGES':
                return {
                    ...state,
                    packages: action.packages,
                };
            case 'SET_PAGES_POSTS':
                return {
                    ...state,
                    pagesPosts: action.pagesPosts,
                };
            case 'SET_PRODUCTS':
                return {
                    ...state,
                    products: action.products,
                };
            case 'SET_VOUCHERS':
                return {
                    ...state,
                    voucherTemplates: action.voucherTemplates,
                };
        }

        return state;
    },
    recrasActions,
    selectors: {
        fetchBookprocesses(state) {
            const { bookprocesses } = state;
            return bookprocesses;
        },
        fetchContactForms(state) {
            const { contactForms } = state;
            return contactForms;
        },
        fetchPackages(state) {
            const { packages } = state;
            return packages;
        },
        fetchPagesPosts(state) {
            const { pagesPosts } = state;
            return pagesPosts;
        },
        fetchProducts(state) {
            const { products } = state;
            return products;
        },
        fetchVoucherTemplates(state) {
            const { voucherTemplates } = state;
            return voucherTemplates;
        },
    },
    controls: {
        FETCH_API(action) {
            return wp.apiFetch({
                path: action.path,
            });
        }
    },
    resolvers: {
        // * makes it a generator function
        * fetchBookprocesses(state) {
            let bookprocesses = yield recrasActions.fetchAPI('recras/bookprocesses');
            bookprocesses = Object.entries(bookprocesses).map(mapBookprocess);

            return recrasActions.setBookprocesses(bookprocesses);
        },
        * fetchContactForms(state) {
            let forms = yield recrasActions.fetchAPI('recras/contactforms');
            forms = Object.entries(forms).map(mapContactForm);

            return recrasActions.setContactForms(forms);
        },
        * fetchPackages(mapSelect, includeEmpty) {
            let packages = yield recrasActions.fetchAPI('recras/packages');
            if (includeEmpty) {
                packages[0] = {
                    arrangement: '',
                    id: 0,
                };
            }
            if (mapSelect) {
                packages = Object.values(packages).map(mapPackage);
            }

            return recrasActions.setPackages(packages);
        },
        * fetchPagesPosts(state) {
            let pagesPosts = [{
                label: '',
                value: '',
            }];

            let page = 1;
            let pages = [];
            let isDone = false;
            while (!isDone) {
                const params = paramsWithPage(page);
                try {
                    let pagesNew = yield recrasActions.fetchAPI('wp/v2/pages?' + params);
                    pages.push(...pagesNew);
                    ++page;
                } catch (e) {
                    if (e.code === 'rest_post_invalid_page_number') {
                        isDone = true;
                    } else {
                        console.warn(e.code);
                    }
                }
            }

            pages = pages.map(p => {
                return mapPagesPosts(p, wp.i18n.__('Page: ', TEXT_DOMAIN));
            });
            pagesPosts = pagesPosts.concat(pages);

            page = 1;
            let posts = [];
            isDone = false;
            while (!isDone) {
                const params = paramsWithPage(page);
                try {
                    let pagesNew = yield recrasActions.fetchAPI('wp/v2/posts?' + params);
                    posts.push(...pagesNew);
                    ++page;
                } catch (e) {
                    if (e.code === 'rest_post_invalid_page_number') {
                        isDone = true;
                    } else {
                        console.warn(e.code);
                    }
                }
            }

            posts = posts.map(p => {
                return mapPagesPosts(p, wp.i18n.__('Post: ', TEXT_DOMAIN));
            });
            pagesPosts = pagesPosts.concat(posts);

            return recrasActions.setPagesPosts(pagesPosts);
        },
        * fetchProducts(state) {
            let products = yield recrasActions.fetchAPI('recras/products');
            products = Object.values(products).map(mapProduct);

            return recrasActions.setProducts(products);
        },
        * fetchVoucherTemplates(state) {
            let vouchers = yield recrasActions.fetchAPI('recras/vouchers');
            vouchers = Object.values(vouchers).map(mapVoucherTemplate);

            return recrasActions.setVoucherTemplates(vouchers);
        },
    }
});
