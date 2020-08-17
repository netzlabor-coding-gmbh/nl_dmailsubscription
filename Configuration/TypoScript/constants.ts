
plugin.tx_nldmailsubscription_sform {
    view {
        # cat=plugin.tx_nldmailsubscription_sform/file; type=string; label=Path to template root (FE)
        templateRootPath = EXT:nl_dmailsubscription/Resources/Private/Templates/
        # cat=plugin.tx_nldmailsubscription_sform/file; type=string; label=Path to template partials (FE)
        partialRootPath = EXT:nl_dmailsubscription/Resources/Private/Partials/
        # cat=plugin.tx_nldmailsubscription_sform/file; type=string; label=Path to template layouts (FE)
        layoutRootPath = EXT:nl_dmailsubscription/Resources/Private/Layouts/
    }
    persistence {
        # cat=plugin.tx_nldmailsubscription_sform//a; type=string; label=Default storage PID
        storagePid =
    }
    settings {
        ajaxTypeNum = 1597222591
        mail {
            # cat=plugin.tx_nldmailsubscription_sform//a; type=string; label=Mail from Name
            fromName =
            # cat=plugin.tx_nldmailsubscription_sform//a; type=string; label=Mail from Email
            fromEmail =
            # cat=plugin.tx_nldmailsubscription_sform//a; type=string; label=Mail Sitename
            sitename =
            # cat=plugin.tx_nldmailsubscription_sform//a; type=string; label=Mail Subscription confirmation subject
            subscriptionConfirmationSubject =
            # cat=plugin.tx_nldmailsubscription_sform//a; type=string; label=Mail Unsubscription confirmation subject
            unsubscriptionConfirmationSubject =
        }
        subscription {
            # cat=plugin.tx_nldmailsubscription_sform//a; type=string; label=Subscription PID
            page =
            confirmation {
                # cat=plugin.tx_nldmailsubscription_sform/enable/a; type=boolean; label=Subscription confirmation enable
                enable = 0
                # cat=plugin.tx_nldmailsubscription_sform//a; type=int; label=Subscription confirmation token lifetime
                tokenLifetime = 86400
            }
            # cat=plugin.tx_nldmailsubscription_sform/enable/a; type=boolean; label=Generate Unsubscription token on subscribe
            generateUnsubscriptionTokenOnSubscribe = 0
            # cat=plugin.tx_nldmailsubscription_sform/enable/a; type=boolean; label=Show Unsubscription link
            showUnsubscriptionLink = 0
            # cat=plugin.tx_nldmailsubscription_sform/links/a; type=string; label=Privacy statement link
            privacyStatementLink =
        }
        unsubscription {
            # cat=plugin.tx_nldmailsubscription_sform//a; type=string; label=Unsubscription PID
            page =
            confirmation {
                # cat=plugin.tx_nldmailsubscription_sform/enable/a; type=boolean; label=Unsubscription confirmation enable
                enable = 0
                # cat=plugin.tx_nldmailsubscription_sform//a; type=int; label=Unsubscription confirmation token lifetime
                tokenLifetime = 86400
            }
        }
    }
}

module.tx_nldmailsubscription_raffleexport {
    view {
        # cat=module.tx_nldmailsubscription_raffleexport/file; type=string; label=Path to template root (BE)
        templateRootPath = EXT:nl_dmailsubscription/Resources/Private/Backend/Templates/
        # cat=module.tx_nldmailsubscription_raffleexport/file; type=string; label=Path to template partials (BE)
        partialRootPath = EXT:nl_dmailsubscription/Resources/Private/Backend/Partials/
        # cat=module.tx_nldmailsubscription_raffleexport/file; type=string; label=Path to template layouts (BE)
        layoutRootPath = EXT:nl_dmailsubscription/Resources/Private/Backend/Layouts/
    }
    persistence {
        # cat=module.tx_nldmailsubscription_raffleexport//a; type=string; label=Default storage PID
        storagePid =
    }
}