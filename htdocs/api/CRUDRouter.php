<?php
// Activate error output...
//@TODO: Move to dedicated Error Reporting or Logging class. Make configurable in greyface.ini!
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);


// Config
// require_once "../../php/Config.php"; // allready in Config Class

// Database
require "../../php/database/DataBase.php";
require "../../php/database/DBException.php";

// Login and User
require "../../php/Login.php";
require "../../php/LoginResult.php";
require "../../php/User.php";

// Stores
require "../../php/stores/AbstractStore.php";
require "../../php/stores/GreylistStore.php";
require "../../php/stores/AutoWhitelistEmailStore.php";
require "../../php/stores/AutoWhitelistDomainStore.php";
require "../../php/stores/WhitelistDomainStore.php";
require "../../php/stores/WhitelistEmailStore.php";
require "../../php/stores/BlacklistDomainStore.php";
require "../../php/stores/BlacklistEmailStore.php";
require "../../php/stores/UserAdminStore.php";
require "../../php/stores/UserAliasStore.php";

// Request Filter
require "../../php/requestFilters/ReadRequestFilter.php";
require "../../php/requestFilters/DeleteGreyfaceEntriesToFilter.php";
require "../../php/requestFilters/GreyfaceEntryFilter.php";
require "../../php/requestFilters/EmailAutoWhitelistFilter.php";
require "../../php/requestFilters/DomainAutoWhitelistFilter.php";
require "../../php/requestFilters/EmailFilter.php";
require "../../php/requestFilters/DomainFilter.php";
require "../../php/requestFilters/UsernameFilter.php";
require "../../php/requestFilters/CreateUserFilter.php";
require "../../php/requestFilters/CreateAliasFilter.php";
require "../../php/requestFilters/DeleteAliasFilter.php";

// AJAX Results
require "../../php/ajaxResult/AjaxResult.php";
require "../../php/ajaxResult/AjaxRowResult.php";

// Set Header!
header("Content-Type: application/json");

// Login user / try to authenticate user
$loginResult = Login::getInstance()->login();

// IF login was successful go on,
// ELSE stop here without dispatching the request!
if($loginResult->getResult()) {
    // Read routing information
    $store = $_GET["store"];
    $action = $_GET["action"];

    echo dispatch($store, $action);
    return;
} else {
    echo new AjaxResult(false, $loginResult->getMsg());
    return;
}

function dispatch($store, $action) {

    $request = ReadRequestFilter::getInstance();


    switch ($store) {
        case "greylistStore":
            switch ($action) {
                case "read":
                    return GreylistStore::getInstance()->getGreylist($request->getLimit(), $request->getStart(), $request->getSortProperty(), $request->getSortDirection(), $request->getFilters());
                case"deleteTo":
                    $greylistEntriesDeleteToDate = DeleteGreyfaceEntriesToFilter::getInstance();
                    return $greylistEntriesDeleteToDate->isDateComplete() ? GreylistStore::getInstance()->deleteTo($greylistEntriesDeleteToDate->getDateTime()) : null;
                case"delete":
                    $greylistDeleteEntry = GreyfaceEntryFilter::getInstance();
                    return $greylistDeleteEntry->isTupelComplete() ? GreylistStore::getInstance()->delete($greylistDeleteEntry->getSenderName(), $greylistDeleteEntry->getDomainName(), $greylistDeleteEntry->getSource(), $greylistDeleteEntry->getRecipient() ) : null;
                case"toWhitelist":
                    $greylistToWhitelist = GreyfaceEntryFilter::getInstance();
                    return $greylistToWhitelist->isTupelComplete() ? GreylistStore::getInstance()->toWhitelist($greylistToWhitelist->getSenderName(), $greylistToWhitelist->getDomainName(), $greylistToWhitelist->getSource(), $greylistToWhitelist->getRecipient() ) : null;
                default:
                    return new AjaxResult(false, AjaxResult::getUnhandledActionMsg());
            }
        case "autoWhitelistEmailStore":
            switch ($action) {
                case "read":
                    return AutoWhitelistEmailStore::getInstance()->getEmails($request->getLimit(), $request->getStart(), $request->getSortProperty(), $request->getSortDirection(), $request->getFilters());
                case "addEmail":
                    $addEmailRequest = EmailAutoWhitelistFilter::getInstance();
                    if ( $addEmailRequest->isTupelComplete() && $addEmailRequest->isValidIp() ) {
                        return AutoWhitelistEmailStore::getInstance()->addEmail(
                            $addEmailRequest->getSender(),
                            $addEmailRequest->getDomain(),
                            $addEmailRequest->getSource()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg()." or ".AjaxResult::getIpInvalidMsg());
                    }
                case "delete":
                    $deleteEmailRequest = EmailAutoWhitelistFilter::getInstance();
                    if ( $deleteEmailRequest->isTupelComplete() ) {
                        return AutoWhitelistEmailStore::getInstance()->deleteEmail(
                            $deleteEmailRequest->getSender(),
                            $deleteEmailRequest->getDomain(),
                            $deleteEmailRequest->getSource()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                default:
                    return new AjaxResult(false, AjaxResult::getUnhandledActionMsg());
            }
        case "autoWhitelistDomainStore":
            switch ($action) {
                case "read":
                    return  AutoWhitelistDomainStore::getInstance()->getDomains($request->getLimit(), $request->getStart(), $request->getSortProperty(), $request->getSortDirection(), $request->getFilters());
                case "addDomain":
                    $addDomainRequest = DomainAutoWhitelistFilter::getInstance();
                    if ( $addDomainRequest->isTupelComplete() ) {
                        return AutoWhitelistDomainStore::getInstance()->addDomain(
                            $addDomainRequest->getDomain(),
                            $addDomainRequest->getSource()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                case "delete":
                    $deleteDomainRequest = DomainAutoWhitelistFilter::getInstance();
                    if ( $deleteDomainRequest->isTupelComplete() ) {
                        return AutoWhitelistDomainStore::getInstance()->deleteDomain(
                            $deleteDomainRequest->getDomain(),
                            $deleteDomainRequest->getSource()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                default:
                    return new AjaxResult(false, AjaxResult::getUnhandledActionMsg());
            }
        case "whitelistEmailStore":
            switch ($action) {
                case "read":
                    return  WhitelistEmailStore::getInstance()->getEmails($request->getLimit(), $request->getStart(), $request->getSortProperty(), $request->getSortDirection(), $request->getFilters());
                case "addEmail":
                    $addEmailRequest = EmailFilter::getInstance();
                    if ( $addEmailRequest->isComplete() ) {
                        return WhitelistEmailStore::getInstance()->addEmail(
                            $addEmailRequest->getEmail()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                case "delete":
                    $deleteEmailRequest = EmailFilter::getInstance();
                    if ( $deleteEmailRequest->isComplete() ) {
                        return WhitelistEmailStore::getInstance()->deleteEmail(
                            $deleteEmailRequest->getEmail()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                default:
                    return new AjaxResult(false, AjaxResult::getUnhandledActionMsg());
            }
        case "whitelistDomainStore":
            switch ($action) {
                case "read":
                    return  WhitelistDomainStore::getInstance()->getDomains($request->getLimit(), $request->getStart(), $request->getSortProperty(), $request->getSortDirection(), $request->getFilters());
                case "addDomain":
                    $addDomainRequest = DomainFilter::getInstance();
                    if ( $addDomainRequest->isComplete() ) {
                        return WhitelistDomainStore::getInstance()->addDomain(
                            $addDomainRequest->getDomain()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                case "delete":
                    $deleteDomainRequest = DomainFilter::getInstance();
                    if ( $deleteDomainRequest->isComplete() ) {
                        return WhitelistDomainStore::getInstance()->deleteDomain(
                            $deleteDomainRequest->getDomain()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                default:
                    return new AjaxResult(false, AjaxResult::getUnhandledActionMsg());
            }
        case "blacklistEmailStore":
            switch ($action) {
                case "read":
                    return  BlacklistEmailStore::getInstance()->getEmails($request->getLimit(), $request->getStart(), $request->getSortProperty(), $request->getSortDirection(), $request->getFilters());
                case "addEmail":
                    $addEmailRequest = EmailFilter::getInstance();
                    if ( $addEmailRequest->isComplete() ) {
                        return BlacklistEmailStore::getInstance()->addEmail(
                            $addEmailRequest->getEmail()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                case "delete":
                    $deleteEmailRequest = EmailFilter::getInstance();
                    if ( $deleteEmailRequest->isComplete() ) {
                        return BlacklistEmailStore::getInstance()->deleteEmail(
                            $deleteEmailRequest->getEmail()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                default:
                    return new AjaxResult(false, AjaxResult::getUnhandledActionMsg());
            }
        case "blacklistDomainStore":
            switch ($action) {
                case "read":
                    return  BlacklistDomainStore::getInstance()->getDomains($request->getLimit(), $request->getStart(), $request->getSortProperty(), $request->getSortDirection(), $request->getFilters());
                case "addDomain":
                    $addEmailRequest = DomainFilter::getInstance();
                    if ( $addEmailRequest->isComplete() ) {
                        return BlacklistDomainStore::getInstance()->addDomain(
                            $addEmailRequest->getDomain()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                case "delete":
                    $deleteEmailRequest = DomainFilter::getInstance();
                    if ( $deleteEmailRequest->isComplete() ) {
                        return BlacklistDomainStore::getInstance()->deleteDomain(
                            $deleteEmailRequest->getDomain()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                default:
                    return new AjaxResult(false, AjaxResult::getUnhandledActionMsg());
            }
        case "userAdminStore":
            switch ($action) {
                case "read":
                    return  UserAdminStore::getInstance()->getUsers($request->getLimit(), $request->getStart(), $request->getSortProperty(), $request->getSortDirection(), $request->getFilters());
                case "addUser":
                    $addUserRequest = CreateUserFilter::getInstance();
                    if ( $addUserRequest->isTupelComplete() ) {
                        return UserAdminStore::getInstance()->addUser(
                            $addUserRequest->getUsername(),
                            $addUserRequest->getEmail(),
                            $addUserRequest->getPassword(),
                            $addUserRequest->isAdmin(),
                            $addUserRequest->isRandomizePassword(),
                            $addUserRequest->isSendEmail()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                case "delete":
                    $deleteUserRequest = UsernameFilter::getInstance();
                    if ( $deleteUserRequest->isComplete() ) {
                        return UserAdminStore::getInstance()->deleteUser(
                            $deleteUserRequest->getUsername()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                case "setPassword":
                    return new AjaxResult(true, "set password...");
                default:
                    return new AjaxResult(false, AjaxResult::getUnhandledActionMsg());
            }
        case "userAliasStore":
            switch ($action) {
                case "read":
                    return  UserAliasStore::getInstance()->getAliases($request->getLimit(), $request->getStart(), $request->getSortProperty(), $request->getSortDirection(), $request->getFilters());
                case "addAlias":
                    $addAliasRequest = CreateAliasFilter::getInstance();
                    if ( $addAliasRequest->isComplete() ) {
                        return UserAliasStore::getInstance()->addAlias(
                            $addAliasRequest->getUsername(),
                            $addAliasRequest->getAlias()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                case "delete":
                    $deleteAliasRequest = DeleteAliasFilter::getInstance();
                    if ( $deleteAliasRequest->isComplete() ) {
                        return UserAliasStore::getInstance()->deleteAlias(
                            $deleteAliasRequest->getAliasId()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                default:
                    return new AjaxResult(false, AjaxResult::getUnhandledActionMsg());
            }
        case "userFilterStore":
            switch ($action) {
                case "getGreylistFilter":
                    return UserAdminStore::getInstance()->getGreylistUserFilterOptions();
                case "getUserAliasFilter":
                    return UserAliasStore::getInstance()->getUserAliasFilterOptions();
                default:
                    return new AjaxResult(false, AjaxResult::getUnhandledActionMsg());
            }
        default:
            return new AjaxResult(false, AjaxResult::getUnhandledActionMsg());
    }
}