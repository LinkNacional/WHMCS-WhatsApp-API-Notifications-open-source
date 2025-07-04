<?php

namespace Lkn\HookNotification\Core\Shared\Infrastructure;

/**
 * @link https://developers.whmcs.com/hooks/hook-index/
 */
enum Hooks: string
{
    case ACCEPT_QUOTE                                    = 'AcceptQuote';
    case ADD_INVOICE_LATE_FEE                            = 'AddInvoiceLateFee';
    case ADD_INVOICE_PAYMENT                             = 'AddInvoicePayment';
    case ADD_TRANSACTION                                 = 'AddTransaction';
    case AFTER_INVOICING_GENERATE_INVOICE_ITEMS          = 'AfterInvoicingGenerateInvoiceItems';
    case CANCEL_AND_REFUND_ORDER                         = 'CancelAndRefundOrder';
    case INVOICE_CANCELLED                               = 'InvoiceCancelled';
    case INVOICE_CHANGE_GATEWAY                          = 'InvoiceChangeGateway';
    case INVOICE_CREATED                                 = 'InvoiceCreated';
    case INVOICE_CREATION                                = 'InvoiceCreation';
    case INVOICE_CREATION_PRE_EMAIL                      = 'InvoiceCreationPreEmail';
    case INVOICE_PAID                                    = 'InvoicePaid';
    case INVOICE_PAID_PRE_EMAIL                          = 'InvoicePaidPreEmail';
    case INVOICE_PAYMENT_REMINDER                        = 'InvoicePaymentReminder';
    case INVOICE_REFUNDED                                = 'InvoiceRefunded';
    case INVOICE_SPLIT                                   = 'InvoiceSplit';
    case INVOICE_UNPAID                                  = 'InvoiceUnpaid';
    case LOG_TRANSACTION                                 = 'LogTransaction';
    case MANUAL_REFUND                                   = 'ManualRefund';
    case PRE_INVOICING_GENERATE_INVOICE_ITEMS            = 'PreInvoicingGenerateInvoiceItems';
    case QUOTE_CREATED                                   = 'QuoteCreated';
    case QUOTE_STATUS_CHANGE                             = 'QuoteStatusChange';
    case UPDATE_INVOICE_TOTAL                            = 'UpdateInvoiceTotal';
    case VIEW_INVOICE_DETAILS_PAGE                       = 'ViewInvoiceDetailsPage';
    case ACCEPT_ORDER                                    = 'AcceptOrder';
    case ADDON_FRAUD                                     = 'AddonFraud';
    case AFTER_CALCULATE_CART_TOTALS                     = 'AfterCalculateCartTotals';
    case AFTER_FRAUD_CHECK                               = 'AfterFraudCheck';
    case AFTER_SHOPPING_CART_CHECKOUT                    = 'AfterShoppingCartCheckout';
    case CANCEL_ORDER                                    = 'CancelOrder';
    case CART_ITEMS_TAX                                  = 'CartItemsTax';
    case CART_SUBDOMAIN_VALIDATION                       = 'CartSubdomainValidation';
    case CART_TOTAL_ADJUSTMENT                           = 'CartTotalAdjustment';
    case DELETE_ORDER                                    = 'DeleteOrder';
    case FRAUD_CHECK_AWAITING_USER_INPUT                 = 'FraudCheckAwaitingUserInput';
    case FRAUD_CHECK_FAILED                              = 'FraudCheckFailed';
    case FRAUD_CHECK_PASSED                              = 'FraudCheckPassed';
    case FRAUD_ORDER                                     = 'FraudOrder';
    case ORDER_ADDON_PRICING_OVERRIDE                    = 'OrderAddonPricingOverride';
    case ORDER_DOMAIN_PRICING_OVERRIDE                   = 'OrderDomainPricingOverride';
    case ORDER_PAID                                      = 'OrderPaid';
    case ORDER_PRODUCT_PRICING_OVERRIDE                  = 'OrderProductPricingOverride';
    case ORDER_PRODUCT_UPGRADE_OVERRIDE                  = 'OrderProductUpgradeOverride';
    case OVERRIDE_ORDER_NUMBER_GENERATION                = 'OverrideOrderNumberGeneration';
    case PENDING_ORDER                                   = 'PendingOrder';
    case PRE_CALCULATE_CART_TOTALS                       = 'PreCalculateCartTotals';
    case PRE_FRAUD_CHECK                                 = 'PreFraudCheck';
    case PRE_SHOPPING_CART_CHECKOUT                      = 'PreShoppingCartCheckout';
    case RUN_FRAUD_CHECK                                 = 'RunFraudCheck';
    case SHOPPING_CART_CHECKOUT_COMPLETE_PAGE            = 'ShoppingCartCheckoutCompletePage';
    case SHOPPING_CART_VALIDATE_CHECKOUT                 = 'ShoppingCartValidateCheckout';
    case SHOPPING_CART_VALIDATE_DOMAIN                   = 'ShoppingCartValidateDomain';
    case SHOPPING_CART_VALIDATE_DOMAINS_CONFIG           = 'ShoppingCartValidateDomainsConfig';
    case SHOPPING_CART_VALIDATE_PRODUCT_UPDATE           = 'ShoppingCartValidateProductUpdate';
    case CANCELLATION_REQUEST                            = 'CancellationRequest';
    case PRE_SERVICE_EDIT                                = 'PreServiceEdit';
    case SERVICE_DELETE                                  = 'ServiceDelete';
    case SERVICE_EDIT                                    = 'ServiceEdit';
    case SERVICE_RECURRING_COMPLETED                     = 'ServiceRecurringCompleted';
    case AFTER_MODULE_CHANGE_PACKAGE                     = 'AfterModuleChangePackage';
    case AFTER_MODULE_CHANGE_PACKAGE_FAILED              = 'AfterModuleChangePackageFailed';
    case AFTER_MODULE_CHANGE_PASSWORD                    = 'AfterModuleChangePassword';
    case AFTER_MODULE_CHANGE_PASSWORD_FAILED             = 'AfterModuleChangePasswordFailed';
    case AFTER_MODULE_CREATE                             = 'AfterModuleCreate';
    case AFTER_MODULE_CREATE_FAILED                      = 'AfterModuleCreateFailed';
    case AFTER_MODULE_CUSTOM                             = 'AfterModuleCustom';
    case AFTER_MODULE_CUSTOM_FAILED                      = 'AfterModuleCustomFailed';
    case AFTER_MODULE_DEPROVISION_ADD_ON_FEATURE         = 'AfterModuleDeprovisionAddOnFeature';
    case AFTER_MODULE_DEPROVISION_ADD_ON_FEATURE_FAILED  = 'AfterModuleDeprovisionAddOnFeatureFailed';
    case AFTER_MODULE_PROVISION_ADD_ON_FEATURE           = 'AfterModuleProvisionAddOnFeature';
    case AFTER_MODULE_PROVISION_ADD_ON_FEATURE_FAILED    = 'AfterModuleProvisionAddOnFeatureFailed';
    case AFTER_MODULE_SUSPEND                            = 'AfterModuleSuspend';
    case AFTER_MODULE_SUSPEND_ADD_ON_FEATURE             = 'AfterModuleSuspendAddOnFeature';
    case AFTER_MODULE_SUSPEND_ADD_ON_FEATURE_FAILED      = 'AfterModuleSuspendAddOnFeatureFailed';
    case AFTER_MODULE_SUSPEND_FAILED                     = 'AfterModuleSuspendFailed';
    case AFTER_MODULE_TERMINATE                          = 'AfterModuleTerminate';
    case AFTER_MODULE_TERMINATE_FAILED                   = 'AfterModuleTerminateFailed';
    case AFTER_MODULE_UNSUSPEND                          = 'AfterModuleUnsuspend';
    case AFTER_MODULE_UNSUSPEND_ADD_ON_FEATURE           = 'AfterModuleUnsuspendAddOnFeature';
    case AFTER_MODULE_UNSUSPEND_ADD_ON_FEATURE_FAILED    = 'AfterModuleUnsuspendAddOnFeatureFailed';
    case AFTER_MODULE_UNSUSPEND_FAILED                   = 'AfterModuleUnsuspendFailed';
    case OVERRIDE_MODULE_USERNAME_GENERATION             = 'OverrideModuleUsernameGeneration';
    case PRE_MODULE_CHANGE_PACKAGE                       = 'PreModuleChangePackage';
    case PRE_MODULE_CHANGE_PASSWORD                      = 'PreModuleChangePassword';
    case PRE_MODULE_CREATE                               = 'PreModuleCreate';
    case PRE_MODULE_CUSTOM                               = 'PreModuleCustom';
    case PRE_MODULE_DEPROVISION_ADD_ON_FEATURE           = 'PreModuleDeprovisionAddOnFeature';
    case PRE_MODULE_PROVISION_ADD_ON_FEATURE             = 'PreModuleProvisionAddOnFeature';
    case PRE_MODULE_RENEW                                = 'PreModuleRenew';
    case PRE_MODULE_SUSPEND                              = 'PreModuleSuspend';
    case PRE_MODULE_SUSPEND_ADD_ON_FEATURE               = 'PreModuleSuspendAddOnFeature';
    case PRE_MODULE_TERMINATE                            = 'PreModuleTerminate';
    case PRE_MODULE_UNSUSPEND                            = 'PreModuleUnsuspend';
    case PRE_MODULE_UNSUSPEND_ADD_ON_FEATURE             = 'PreModuleUnsuspendAddOnFeature';
    case DOMAIN_DELETE                                   = 'DomainDelete';
    case DOMAIN_EDIT                                     = 'DomainEdit';
    case DOMAIN_TRANSFER_COMPLETED                       = 'DomainTransferCompleted';
    case DOMAIN_TRANSFER_FAILED                          = 'DomainTransferFailed';
    case DOMAIN_VALIDATION                               = 'DomainValidation';
    case PRE_DOMAIN_REGISTER                             = 'PreDomainRegister';
    case PRE_DOMAIN_TRANSFER                             = 'PreDomainTransfer';
    case PRE_REGISTRAR_REGISTER_DOMAIN                   = 'PreRegistrarRegisterDomain';
    case PRE_REGISTRAR_RENEW_DOMAIN                      = 'PreRegistrarRenewDomain';
    case PRE_REGISTRAR_TRANSFER_DOMAIN                   = 'PreRegistrarTransferDomain';
    case TOP_LEVEL_DOMAIN_ADD                            = 'TopLevelDomainAdd';
    case TOP_LEVEL_DOMAIN_DELETE                         = 'TopLevelDomainDelete';
    case TOP_LEVEL_DOMAIN_PRICING_UPDATE                 = 'TopLevelDomainPricingUpdate';
    case TOP_LEVEL_DOMAIN_UPDATE                         = 'TopLevelDomainUpdate';
    case AFTER_REGISTRAR_GET_CONTACT_DETAILS             = 'AfterRegistrarGetContactDetails';
    case AFTER_REGISTRAR_GET_DNS                         = 'AfterRegistrarGetDNS';
    case AFTER_REGISTRAR_GET_EPP_CODE                    = 'AfterRegistrarGetEPPCode';
    case AFTER_REGISTRAR_GET_NAMESERVERS                 = 'AfterRegistrarGetNameservers';
    case AFTER_REGISTRAR_REGISTER                        = 'AfterRegistrarRegister';
    case AFTER_REGISTRAR_REGISTRATION                    = 'AfterRegistrarRegistration';
    case AFTER_REGISTRAR_REGISTRATION_FAILED             = 'AfterRegistrarRegistrationFailed';
    case AFTER_REGISTRAR_RENEW                           = 'AfterRegistrarRenew';
    case AFTER_REGISTRAR_RENEWAL                         = 'AfterRegistrarRenewal';
    case AFTER_REGISTRAR_RENEWAL_FAILED                  = 'AfterRegistrarRenewalFailed';
    case AFTER_REGISTRAR_REQUEST_DELETE                  = 'AfterRegistrarRequestDelete';
    case AFTER_REGISTRAR_SAVE_CONTACT_DETAILS            = 'AfterRegistrarSaveContactDetails';
    case AFTER_REGISTRAR_SAVE_DNS                        = 'AfterRegistrarSaveDNS';
    case AFTER_REGISTRAR_SAVE_NAMESERVERS                = 'AfterRegistrarSaveNameservers';
    case AFTER_REGISTRAR_TRANSFER                        = 'AfterRegistrarTransfer';
    case AFTER_REGISTRAR_TRANSFER_FAILED                 = 'AfterRegistrarTransferFailed';
    case PRE_REGISTRAR_GET_CONTACT_DETAILS               = 'PreRegistrarGetContactDetails';
    case PRE_REGISTRAR_GET_DNS                           = 'PreRegistrarGetDNS';
    case PRE_REGISTRAR_GET_EPP_CODE                      = 'PreRegistrarGetEPPCode';
    case PRE_REGISTRAR_GET_NAMESERVERS                   = 'PreRegistrarGetNameservers';
    case PRE_REGISTRAR_REQUEST_DELETE                    = 'PreRegistrarRequestDelete';
    case PRE_REGISTRAR_SAVE_CONTACT_DETAILS              = 'PreRegistrarSaveContactDetails';
    case PRE_REGISTRAR_SAVE_DNS                          = 'PreRegistrarSaveDNS';
    case PRE_REGISTRAR_SAVE_NAMESERVERS                  = 'PreRegistrarSaveNameservers';
    case ADDON                                           = 'Addon';
    case ADDON_ACTIVATED                                 = 'AddonActivated';
    case ADDON_ACTIVATION                                = 'AddonActivation';
    case ADDON_ADD                                       = 'AddonAdd';
    case ADDON_CANCELLED                                 = 'AddonCancelled';
    case ADDON_CONFIG                                    = 'AddonConfig';
    case ADDON_CONFIG_SAVE                               = 'AddonConfigSave';
    case ADDON_DELETED                                   = 'AddonDeleted';
    case ADDON_EDIT                                      = 'AddonEdit';
    case ADDON_RENEWAL                                   = 'AddonRenewal';
    case ADDON_SUSPENDED                                 = 'AddonSuspended';
    case ADDON_TERMINATED                                = 'AddonTerminated';
    case ADDON_UNSUSPENDED                               = 'AddonUnsuspended';
    case AFTER_ADDON_UPGRADE                             = 'AfterAddonUpgrade';
    case LICENSING_ADDON_REISSUE                         = 'LicensingAddonReissue';
    case LICENSING_ADDON_VERIFY                          = 'LicensingAddonVerify';
    case PRODUCT_ADDON_DELETE                            = 'ProductAddonDelete';
    case AFTER_CLIENT_MERGE                              = 'AfterClientMerge';
    case CLIENT_ADD                                      = 'ClientAdd';
    case CLIENT_ALERT                                    = 'ClientAlert';
    case CLIENT_CHANGE_PASSWORD                          = 'ClientChangePassword';
    case CLIENT_CLOSE                                    = 'ClientClose';
    case CLIENT_DELETE                                   = 'ClientDelete';
    case CLIENT_DETAILS_VALIDATION                       = 'ClientDetailsValidation';
    case CLIENT_EDIT                                     = 'ClientEdit';
    case PRE_DELETE_CLIENT                               = 'PreDeleteClient';
    case USER_ADD                                        = 'UserAdd';
    case USER_CHANGE_PASSWORD                            = 'UserChangePassword';
    case USER_EDIT                                       = 'UserEdit';
    case USER_EMAIL_VERIFICATION_COMPLETE                = 'UserEmailVerificationComplete';
    case CONTACT_ADD                                     = 'ContactAdd';
    case CONTACT_DELETE                                  = 'ContactDelete';
    case CONTACT_DETAILS_VALIDATION                      = 'ContactDetailsValidation';
    case CONTACT_EDIT                                    = 'ContactEdit';
    case AFTER_PRODUCT_UPGRADE                           = 'AfterProductUpgrade';
    case PRODUCT_DELETE                                  = 'ProductDelete';
    case PRODUCT_EDIT                                    = 'ProductEdit';
    case SERVER_ADD                                      = 'ServerAdd';
    case SERVER_DELETE                                   = 'ServerDelete';
    case SERVER_EDIT                                     = 'ServerEdit';
    case ADMIN_AREA_VIEW_TICKET_PAGE                     = 'AdminAreaViewTicketPage';
    case ADMIN_AREA_VIEW_TICKET_PAGE_SIDEBAR             = 'AdminAreaViewTicketPageSidebar';
    case ADMIN_SUPPORT_TICKET_PAGE_PRE_TICKETS           = 'AdminSupportTicketPagePreTickets';
    case CLIENT_AREA_PAGE_SUBMIT_TICKET                  = 'ClientAreaPageSubmitTicket';
    case CLIENT_AREA_PAGE_SUPPORT_TICKETS                = 'ClientAreaPageSupportTickets';
    case CLIENT_AREA_PAGE_VIEW_TICKET                    = 'ClientAreaPageViewTicket';
    case SUBMIT_TICKET_ANSWER_SUGGESTIONS                = 'SubmitTicketAnswerSuggestions';
    case TICKET_ADD_NOTE                                 = 'TicketAddNote';
    case TICKET_ADMIN_REPLY                              = 'TicketAdminReply';
    case TICKET_CLOSE                                    = 'TicketClose';
    case TICKET_DELETE                                   = 'TicketDelete';
    case TICKET_DELETE_REPLY                             = 'TicketDeleteReply';
    case TICKET_DEPARTMENT_CHANGE                        = 'TicketDepartmentChange';
    case TICKET_FLAGGED                                  = 'TicketFlagged';
    case TICKET_MERGE                                    = 'TicketMerge';
    case TICKET_OPEN                                     = 'TicketOpen';
    case TICKET_OPEN_ADMIN                               = 'TicketOpenAdmin';
    case TICKET_OPEN_VALIDATION                          = 'TicketOpenValidation';
    case TICKET_PIPING                                   = 'TicketPiping';
    case TICKET_PRIORITY_CHANGE                          = 'TicketPriorityChange';
    case TICKET_SPLIT                                    = 'TicketSplit';
    case TICKET_STATUS_CHANGE                            = 'TicketStatusChange';
    case TICKET_SUBJECT_CHANGE                           = 'TicketSubjectChange';
    case TICKET_USER_REPLY                               = 'TicketUserReply';
    case TRANSLITERATE_TICKET_TEXT                       = 'TransliterateTicketText';
    case ANNOUNCEMENT_ADD                                = 'AnnouncementAdd';
    case ANNOUNCEMENT_EDIT                               = 'AnnouncementEdit';
    case FILE_DOWNLOAD                                   = 'FileDownload';
    case NETWORK_ISSUE_ADD                               = 'NetworkIssueAdd';
    case NETWORK_ISSUE_CLOSE                             = 'NetworkIssueClose';
    case NETWORK_ISSUE_DELETE                            = 'NetworkIssueDelete';
    case NETWORK_ISSUE_EDIT                              = 'NetworkIssueEdit';
    case NETWORK_ISSUE_REOPEN                            = 'NetworkIssueReopen';
    case CLIENT_LOGIN_SHARE                              = 'ClientLoginShare';
    case USER_LOGIN                                      = 'UserLogin';
    case USER_LOGOUT                                     = 'UserLogout';
    case CLIENT_AREA_DOMAIN_DETAILS                      = 'ClientAreaDomainDetails';
    case CLIENT_AREA_HOMEPAGE                            = 'ClientAreaHomepage';
    case CLIENT_AREA_HOMEPAGE_PANELS                     = 'ClientAreaHomepagePanels';
    case CLIENT_AREA_NAVBARS                             = 'ClientAreaNavbars';
    case CLIENT_AREA_PAGE                                = 'ClientAreaPage';
    case CLIENT_AREA_PAGE_ADD_CONTACT                    = 'ClientAreaPageAddContact';
    case CLIENT_AREA_PAGE_ADD_FUNDS                      = 'ClientAreaPageAddFunds';
    case CLIENT_AREA_PAGE_ADDON_MODULE                   = 'ClientAreaPageAddonModule';
    case CLIENT_AREA_PAGE_AFFILIATES                     = 'ClientAreaPageAffiliates';
    case CLIENT_AREA_PAGE_ANNOUNCEMENTS                  = 'ClientAreaPageAnnouncements';
    case CLIENT_AREA_PAGE_BANNED                         = 'ClientAreaPageBanned';
    case CLIENT_AREA_PAGE_BULK_DOMAIN_MANAGEMENT         = 'ClientAreaPageBulkDomainManagement';
    case CLIENT_AREA_PAGE_CANCELLATION                   = 'ClientAreaPageCancellation';
    case CLIENT_AREA_PAGE_CART                           = 'ClientAreaPageCart';
    case CLIENT_AREA_PAGE_CHANGE_PASSWORD                = 'ClientAreaPageChangePassword';
    case CLIENT_AREA_PAGE_CONFIGURE_SSL                  = 'ClientAreaPageConfigureSSL';
    case CLIENT_AREA_PAGE_CONTACT                        = 'ClientAreaPageContact';
    case CLIENT_AREA_PAGE_CONTACTS                       = 'ClientAreaPageContacts';
    case CLIENT_AREA_PAGE_CREDIT_CARD                    = 'ClientAreaPageCreditCard';
    case CLIENT_AREA_PAGE_CREDIT_CARD_CHECKOUT           = 'ClientAreaPageCreditCardCheckout';
    case CLIENT_AREA_PAGE_DOMAIN_ADDONS                  = 'ClientAreaPageDomainAddons';
    case CLIENT_AREA_PAGE_DOMAIN_CONTACTS                = 'ClientAreaPageDomainContacts';
    case CLIENT_AREA_PAGE_DOMAIN_DNS_MANAGEMENT          = 'ClientAreaPageDomainDNSManagement';
    case CLIENT_AREA_PAGE_DOMAIN_DETAILS                 = 'ClientAreaPageDomainDetails';
    case CLIENT_AREA_PAGE_DOMAIN_EPP_CODE                = 'ClientAreaPageDomainEPPCode';
    case CLIENT_AREA_PAGE_DOMAIN_EMAIL_FORWARDING        = 'ClientAreaPageDomainEmailForwarding';
    case CLIENT_AREA_PAGE_DOMAIN_REGISTER_NAMESERVERS    = 'ClientAreaPageDomainRegisterNameservers';
    case CLIENT_AREA_PAGE_DOMAINS                        = 'ClientAreaPageDomains';
    case CLIENT_AREA_PAGE_DOWNLOADS                      = 'ClientAreaPageDownloads';
    case CLIENT_AREA_PAGE_EMAILS                         = 'ClientAreaPageEmails';
    case CLIENT_AREA_PAGE_HOME                           = 'ClientAreaPageHome';
    case CLIENT_AREA_PAGE_INVOICES                       = 'ClientAreaPageInvoices';
    case CLIENT_AREA_PAGE_KNOWLEDGEBASE                  = 'ClientAreaPageKnowledgebase';
    case CLIENT_AREA_PAGE_LOGIN                          = 'ClientAreaPageLogin';
    case CLIENT_AREA_PAGE_LOGOUT                         = 'ClientAreaPageLogout';
    case CLIENT_AREA_PAGE_MASS_PAY                       = 'ClientAreaPageMassPay';
    case CLIENT_AREA_PAGE_NETWORK_ISSUES                 = 'ClientAreaPageNetworkIssues';
    case CLIENT_AREA_PAGE_PASSWORD_RESET                 = 'ClientAreaPagePasswordReset';
    case CLIENT_AREA_PAGE_PRODUCT_DETAILS                = 'ClientAreaPageProductDetails';
    case CLIENT_AREA_PAGE_PRODUCTS_SERVICES              = 'ClientAreaPageProductsServices';
    case CLIENT_AREA_PAGE_PROFILE                        = 'ClientAreaPageProfile';
    case CLIENT_AREA_PAGE_QUOTES                         = 'ClientAreaPageQuotes';
    case CLIENT_AREA_PAGE_REGISTER                       = 'ClientAreaPageRegister';
    case CLIENT_AREA_PAGE_SECURITY                       = 'ClientAreaPageSecurity';
    case CLIENT_AREA_PAGE_SERVER_STATUS                  = 'ClientAreaPageServerStatus';
    case CLIENT_AREA_PAGE_UNSUBSCRIBE                    = 'ClientAreaPageUnsubscribe';
    case CLIENT_AREA_PAGE_UPGRADE                        = 'ClientAreaPageUpgrade';
    case CLIENT_AREA_PAGE_VIEW_EMAIL                     = 'ClientAreaPageViewEmail';
    case CLIENT_AREA_PAGE_VIEW_INVOICE                   = 'ClientAreaPageViewInvoice';
    case CLIENT_AREA_PAGE_VIEW_QUOTE                     = 'ClientAreaPageViewQuote';
    case CLIENT_AREA_PAYMENT_METHODS                     = 'ClientAreaPaymentMethods';
    case CLIENT_AREA_PRIMARY_NAVBAR                      = 'ClientAreaPrimaryNavbar';
    case CLIENT_AREA_PRIMARY_SIDEBAR                     = 'ClientAreaPrimarySidebar';
    case CLIENT_AREA_PRODUCT_DETAILS                     = 'ClientAreaProductDetails';
    case CLIENT_AREA_PRODUCT_DETAILS_PRE_MODULE_TEMPLATE = 'ClientAreaProductDetailsPreModuleTemplate';
    case CLIENT_AREA_REGISTER                            = 'ClientAreaRegister';
    case CLIENT_AREA_SECONDARY_NAVBAR                    = 'ClientAreaSecondaryNavbar';
    case CLIENT_AREA_SECONDARY_SIDEBAR                   = 'ClientAreaSecondarySidebar';
    case CLIENT_AREA_SIDEBARS                            = 'ClientAreaSidebars';
    case ADMIN_AREA_CLIENT_SUMMARY_ACTION_LINKS          = 'AdminAreaClientSummaryActionLinks';
    case ADMIN_AREA_CLIENT_SUMMARY_PAGE                  = 'AdminAreaClientSummaryPage';
    case ADMIN_AREA_PAGE                                 = 'AdminAreaPage';
    case ADMIN_AREA_VIEW_QUOTE_PAGE                      = 'AdminAreaViewQuotePage';
    case ADMIN_CLIENT_DOMAINS_TAB_FIELDS                 = 'AdminClientDomainsTabFields';
    case ADMIN_CLIENT_DOMAINS_TAB_FIELDS_SAVE            = 'AdminClientDomainsTabFieldsSave';
    case ADMIN_CLIENT_FILE_UPLOAD                        = 'AdminClientFileUpload';
    case ADMIN_CLIENT_PROFILE_TAB_FIELDS                 = 'AdminClientProfileTabFields';
    case ADMIN_CLIENT_PROFILE_TAB_FIELDS_SAVE            = 'AdminClientProfileTabFieldsSave';
    case ADMIN_CLIENT_SERVICES_TAB_FIELDS                = 'AdminClientServicesTabFields';
    case ADMIN_CLIENT_SERVICES_TAB_FIELDS_SAVE           = 'AdminClientServicesTabFieldsSave';
    case ADMIN_HOMEPAGE                                  = 'AdminHomepage';
    case ADMIN_LOGIN                                     = 'AdminLogin';
    case ADMIN_LOGOUT                                    = 'AdminLogout';
    case ADMIN_PREDEFINED_ADDONS                         = 'AdminPredefinedAddons';
    case ADMIN_PRODUCT_CONFIG_FIELDS                     = 'AdminProductConfigFields';
    case ADMIN_PRODUCT_CONFIG_FIELDS_SAVE                = 'AdminProductConfigFieldsSave';
    case ADMIN_SERVICE_EDIT                              = 'AdminServiceEdit';
    case AUTH_ADMIN                                      = 'AuthAdmin';
    case AUTH_ADMIN_API                                  = 'AuthAdminApi';
    case INVOICE_CREATION_ADMIN_AREA                     = 'InvoiceCreationAdminArea';
    case PRE_ADMIN_SERVICE_EDIT                          = 'PreAdminServiceEdit';
    case VIEW_ORDER_DETAILS_PAGE                         = 'ViewOrderDetailsPage';
    case ADMIN_AREA_FOOTER_OUTPUT                        = 'AdminAreaFooterOutput';
    case ADMIN_AREA_HEAD_OUTPUT                          = 'AdminAreaHeadOutput';
    case ADMIN_AREA_HEADER_OUTPUT                        = 'AdminAreaHeaderOutput';
    case ADMIN_INVOICES_CONTROLS_OUTPUT                  = 'AdminInvoicesControlsOutput';
    case CLIENT_AREA_DOMAIN_DETAILS_OUTPUT               = 'ClientAreaDomainDetailsOutput';
    case CLIENT_AREA_FOOTER_OUTPUT                       = 'ClientAreaFooterOutput';
    case CLIENT_AREA_HEAD_OUTPUT                         = 'ClientAreaHeadOutput';
    case CLIENT_AREA_HEADER_OUTPUT                       = 'ClientAreaHeaderOutput';
    case CLIENT_AREA_PRODUCT_DETAILS_OUTPUT              = 'ClientAreaProductDetailsOutput';
    case FORMAT_DATE_FOR_CLIENT_AREA_OUTPUT              = 'FormatDateForClientAreaOutput';
    case FORMAT_DATE_TIME_FOR_CLIENT_AREA_OUTPUT         = 'FormatDateTimeForClientAreaOutput';
    case REPORT_VIEW_POST_OUTPUT                         = 'ReportViewPostOutput';
    case REPORT_VIEW_PRE_OUTPUT                          = 'ReportViewPreOutput';
    case SHOPPING_CART_CHECKOUT_OUTPUT                   = 'ShoppingCartCheckoutOutput';
    case SHOPPING_CART_CONFIGURE_PRODUCT_ADDONS_OUTPUT   = 'ShoppingCartConfigureProductAddonsOutput';
    case SHOPPING_CART_VIEW_CART_OUTPUT                  = 'ShoppingCartViewCartOutput';
    case AFTER_CRON_JOB                                  = 'AfterCronJob';
    case DAILY_CRON_JOB                                  = 'DailyCronJob';
    case DAILY_CRON_JOB_PRE_EMAIL                        = 'DailyCronJobPreEmail';
    case POP_EMAIL_COLLECTION_CRON_COMPLETED             = 'PopEmailCollectionCronCompleted';
    case POST_AUTOMATION_TASK                            = 'PostAutomationTask';
    case PRE_AUTOMATION_TASK                             = 'PreAutomationTask';
    case PRE_CRON_JOB                                    = 'PreCronJob';
    case AFFILIATE_ACTIVATION                            = 'AffiliateActivation';
    case AFFILIATE_CLICKTHRU                             = 'AffiliateClickthru';
    case AFFILIATE_COMMISSION                            = 'AffiliateCommission';
    case AFFILIATE_WITHDRAWAL_REQUEST                    = 'AffiliateWithdrawalRequest';
    case AFTER_CONFIG_OPTIONS_UPGRADE                    = 'AfterConfigOptionsUpgrade';
    case CC_UPDATE                                       = 'CCUpdate';
    case CALC_AFFILIATE_COMMISSION                       = 'CalcAffiliateCommission';
    case CUSTOM_FIELD_LOAD                               = 'CustomFieldLoad';
    case CUSTOM_FIELD_SAVE                               = 'CustomFieldSave';
    case EMAIL_PRE_LOG                                   = 'EmailPreLog';
    case EMAIL_PRE_SEND                                  = 'EmailPreSend';
    case EMAIL_TPL_MERGE_FIELDS                          = 'EmailTplMergeFields';
    case FETCH_CURRENCY_EXCHANGE_RATES                   = 'FetchCurrencyExchangeRates';
    case INTELLIGENT_SEARCH                              = 'IntelligentSearch';
    case LINK_TRACKER                                    = 'LinkTracker';
    case LOG_ACTIVITY                                    = 'LogActivity';
    case NOTIFICATION_PRE_SEND                           = 'NotificationPreSend';
    case PAY_METHOD_MIGRATION                            = 'PayMethodMigration';
    case PRE_EMAIL_SEND_REDUCE_RECIPIENTS                = 'PreEmailSendReduceRecipients';
    case PRE_UPGRADE_CHECKOUT                            = 'PreUpgradeCheckout';
    case PREMIUM_PRICE_OVERRIDE                          = 'PremiumPriceOverride';
    case PREMIUM_PRICE_RECALCULATION_OVERRIDE            = 'PremiumPriceRecalculationOverride';
    case LKN_HN_MANUAL                                   = 'LKNHNManual';
    case BULK                                            = 'LKNHNBulk';
}
