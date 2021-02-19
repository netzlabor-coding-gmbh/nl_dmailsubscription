
plugin.tx_nldmailsubscription_sform {
    view {
        templateRootPaths.0 = EXT:nl_dmailsubscription/Resources/Private/Templates/
        templateRootPaths.1 = {$plugin.tx_nldmailsubscription_sform.view.templateRootPath}
        partialRootPaths.0 = EXT:nl_dmailsubscription/Resources/Private/Partials/
        partialRootPaths.1 = {$plugin.tx_nldmailsubscription_sform.view.partialRootPath}
        layoutRootPaths.0 = EXT:nl_dmailsubscription/Resources/Private/Layouts/
        layoutRootPaths.1 = {$plugin.tx_nldmailsubscription_sform.view.layoutRootPath}
    }
    persistence {
        storagePid = {$plugin.tx_nldmailsubscription_sform.persistence.storagePid}
        #recursive = 1
    }
    settings {
        ajaxTypeNum = {$plugin.tx_nldmailsubscription_sform.settings.ajaxTypeNum}
        mail {
            fromName = {$plugin.tx_nldmailsubscription_sform.settings.mail.fromName}
            fromEmail = {$plugin.tx_nldmailsubscription_sform.settings.mail.fromEmail}
            sitename = {$plugin.tx_nldmailsubscription_sform.settings.mail.sitename}
            subscriptionConfirmationSubject = {$plugin.tx_nldmailsubscription_sform.settings.mail.subscriptionConfirmationSubject}
            unsubscriptionConfirmationSubject = {$plugin.tx_nldmailsubscription_sform.settings.mail.unsubscriptionConfirmationSubject}
        }
        subscription {
            page = {$plugin.tx_nldmailsubscription_sform.settings.subscription.page}
            confirmation {
                enable = {$plugin.tx_nldmailsubscription_sform.settings.subscription.confirmation.enable}
                tokenLifetime = {$plugin.tx_nldmailsubscription_sform.settings.subscription.confirmation.tokenLifetime}
            }
            generateUnsubscriptionTokenOnSubscribe = {$plugin.tx_nldmailsubscription_sform.settings.subscription.generateUnsubscriptionTokenOnSubscribe}
            showUnsubscriptionLink = {$plugin.tx_nldmailsubscription_sform.settings.subscription.showUnsubscriptionLink}
            privacyStatementLink = {$plugin.tx_nldmailsubscription_sform.settings.subscription.privacyStatementLink}
        }
        unsubscription {
            page = {$plugin.tx_nldmailsubscription_sform.settings.unsubscription.page}
            confirmation {
                enable = {$plugin.tx_nldmailsubscription_sform.settings.unsubscription.confirmation.enable}
                tokenLifetime = {$plugin.tx_nldmailsubscription_sform.settings.unsubscription.confirmation.tokenLifetime}
            }
        }
    }
    features {
        #skipDefaultArguments = 1
        # if set to 1, the enable fields are ignored in BE context
        ignoreAllEnableFieldsInBe = 0
        # Should be on by default, but can be disabled if all action in the plugin are uncached
        requireCHashArgumentForActionArguments = 1
    }
    mvc {
        #callDefaultActionIfActionCantBeResolved = 1
    }
}

nldmailsubscription_sform_AjaxResponse = PAGE
nldmailsubscription_sform_AjaxResponse {
    typeNum = {$plugin.tx_nldmailsubscription_sform.settings.ajaxTypeNum}

    headerData >
    config {
        no_cache = 1
        disableAllHeaderCode = 1
        additionalHeaders.10.header = Content-Type: application/json
        additionalHeaders.10.replace = 1
        debug = 0
        admPanel = 0
    }

    10 < styles.content.get
    10 {
        stdWrap.trim = 1
        select {
            where = list_type = "nldmailsubscription_sform"
        }

        renderObj < tt_content.list.20.nldmailsubscription_sform
    }
}

# these classes are only used in auto-generated templates
plugin.tx_nldmailsubscription._CSS_DEFAULT_STYLE (
    textarea.f3-form-error {
        background-color:#FF9F9F;
        border: 1px #FF0000 solid;
    }

    input.f3-form-error {
        background-color:#FF9F9F;
        border: 1px #FF0000 solid;
    }

    .tx-dmail-subscription table {
        border-collapse:separate;
        border-spacing:10px;
    }

    .tx-dmail-subscription table th {
        font-weight:bold;
    }

    .tx-dmail-subscription table td {
        vertical-align:top;
    }

    .typo3-messages .message-error {
        color:red;
    }

    .typo3-messages .message-ok {
        color:green;
    }
)
