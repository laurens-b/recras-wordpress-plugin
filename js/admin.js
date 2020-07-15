/**
 * Disable arrangements that are not allowed for this contact form
 *
 * @param {Array} packageIDs List of package IDs
 */
function disableNotAllowed(packageIDs)
{
    let options = document.getElementById('arrangement_id').getElementsByTagName('option');
    for (let i = 0; i < options.length; i++) {
        options[i].disabled = (packageIDs.indexOf(parseInt(options[i].value,10)) === -1);
    }
}

/*
 * Get packages that are valid for a given contact form
 *
 * @param {Number} formID
 * @param {String} subdomain
 */
function getContactFormArrangements(formID, subdomain)
{
    if (!formID) {
        return false;
    }

    let lastResponse;
    fetch(`https://${subdomain}.recras.nl/api2.php/contactformulieren/${formID}/arrangementen`)
        .then(res => {
            lastResponse = res;
            return res.json();
        })
        .then(json => {
            if (!lastResponse.ok) {
                alert(recras_l10n.no_connection);
                return;
            }
            let contactFormPackages = json.map(i => i.arrangement_id);
            disableNotAllowed(contactFormPackages);
        })
        .catch(res => {
            alert(recras_l10n.no_connection);
        });
}
