<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

// Error handling
require "../../php/errorHandler/Handler.php";


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
require "../../php/requestFilters/AbstractAjaxRequestFilterPost.php";

require "../../php/requestFilters/ReadRequestFilterGet.php";

require "../../php/requestFilters/GreyfaceEntryFilterPost.php";
require "../../php/requestFilters/EmailAutoWhitelistFilterPost.php";
require "../../php/requestFilters/DomainAutoWhitelistFilterPost.php";
require "../../php/requestFilters/EmailFilterPost.php";
require "../../php/requestFilters/DomainFilterPost.php";


require "../../php/requestFilters/CreateAliasFilterPost.php";
require "../../php/requestFilters/CreateUserFilterPost.php";

require "../../php/requestFilters/DeleteUserFilterPost.php";
require "../../php/requestFilters/DeleteGreyfaceEntriesToFilterPost.php";
require "../../php/requestFilters/DeleteAliasFilterPost.php";


require "../../php/requestFilters/UpdateUserPasswordFilterPost.php";
require "../../php/requestFilters/UpdateUserFilterPost.php";
require "../../php/requestFilters/UpdateAliasFilterPost.php";
require "../../php/requestFilters/UpdateEmailFilterPost.php";
require "../../php/requestFilters/UpdateDomainFilterPost.php";
require "../../php/requestFilters/UpdateAutoWhitelistEmailPost.php";
require "../../php/requestFilters/UpdateAutoWhitelistDomainPost.php";

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
    if ( array_key_exists('action', $_GET) && array_key_exists('store', $_GET) ) {
        $store = $_GET["store"];
        $action = $_GET["action"];
    } else if( array_key_exists('action', $_GET) && array_key_exists('store', $_POST)) {
        $store = $_POST["store"];
        $action = $_GET["action"];
    } else {
        return new AjaxResult(false, AjaxResult::getWrongRoutingMsg());
    }
    echo dispatch($store, $action, $loginResult->getUser());
    return;
} else {
    echo new AjaxResult(false, $loginResult->getMsg());
    return;
}

function dispatch($store, $action, User $loggedInUser) {

    $request = ReadRequestFilter::getInstance();


    switch ($store) {
        case "greylistStore":
            switch ($action) {
                case "read":
                    return GreylistStore::getInstance()->getGreylist($request->getLimit(), $request->getStart(), $request->getSortProperty(), $request->getSortDirection(), $request->getFilters());
                case"deleteTo":
                    $greylistEntriesDeleteToDate = DeleteGreyfaceEntriesToFilterPost::getInstance();
                    if( $greylistEntriesDeleteToDate->isDateComplete() ) {
                        return GreylistStore::getInstance()->deleteTo($greylistEntriesDeleteToDate->getDateTime());
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                case"delete":
                    $greylistDeleteEntry = GreyfaceEntryFilterPost::getInstance();
                    if ( $greylistDeleteEntry->isComplete() ) {
                        return GreylistStore::getInstance()->delete(
                            $greylistDeleteEntry->getSenderName(),
                            $greylistDeleteEntry->getDomainName(),
                            $greylistDeleteEntry->getSource(),
                            $greylistDeleteEntry->getRecipient()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                case"toWhitelist":
                    $greylistToWhitelist = GreyfaceEntryFilterPost::getInstance();
                    if( $greylistToWhitelist->isComplete() ) {
                        return GreylistStore::getInstance()->toWhitelist(
                            $greylistToWhitelist->getSenderName(),
                            $greylistToWhitelist->getDomainName(),
                            $greylistToWhitelist->getSource(),
                            $greylistToWhitelist->getRecipient()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                default:
                    return new AjaxResult(false, AjaxResult::getUnhandledActionMsg());
            }
        case "autoWhitelistEmailStore":

            if(!$loggedInUser->isAdmin()) {
                return new AjaxResult(false, AjaxResult::getAccessDeniedMsg());
            }

            switch ($action) {
                case "read":
                    return AutoWhitelistEmailStore::getInstance()->getEmails($request->getLimit(), $request->getStart(), $request->getSortProperty(), $request->getSortDirection(), $request->getFilters());
                case "addEmail":
                    $addEmailRequest = EmailAutoWhitelistFilterPost::getInstance();
                    if ( $addEmailRequest->isComplete() ) {
                        return AutoWhitelistEmailStore::getInstance()->addEmail(
                            $addEmailRequest->getSender(),
                            $addEmailRequest->getDomain(),
                            $addEmailRequest->getSource()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                case "delete":
                    $deleteEmailRequest = EmailAutoWhitelistFilterPost::getInstance();
                    if ( $deleteEmailRequest->isComplete() ) {
                        return AutoWhitelistEmailStore::getInstance()->deleteEmail(
                            $deleteEmailRequest->getSender(),
                            $deleteEmailRequest->getDomain(),
                            $deleteEmailRequest->getSource()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                case "update":
                    $updateEmailRequest = UpdateAutoWhitelistEmailPost::getInstance();
                    if( $updateEmailRequest->isComplete() ) {
                        return AutoWhitelistEmailStore::getInstance()->updateEmail(
                            $updateEmailRequest->getSenderName(),
                            $updateEmailRequest->getSenderDomain(),
                            $updateEmailRequest->getSrc(),
                            $updateEmailRequest->getSenderNameId(),
                            $updateEmailRequest->getSenderDomainId(),
                            $updateEmailRequest->getSrcId()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                default:
                    return new AjaxResult(false, AjaxResult::getUnhandledActionMsg());
            }
        case "autoWhitelistDomainStore":

            if(!$loggedInUser->isAdmin()) {
                return new AjaxResult(false, AjaxResult::getAccessDeniedMsg());
            }

            switch ($action) {
                case "read":
                    return  AutoWhitelistDomainStore::getInstance()->getDomains($request->getLimit(), $request->getStart(), $request->getSortProperty(), $request->getSortDirection(), $request->getFilters());
                case "addDomain":
                    $addDomainRequest = DomainAutoWhitelistFilterPost::getInstance();
                    if ( $addDomainRequest->isComplete() ) {
                        return AutoWhitelistDomainStore::getInstance()->addDomain(
                            $addDomainRequest->getDomain(),
                            $addDomainRequest->getSource()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                case "delete":
                    $deleteDomainRequest = DomainAutoWhitelistFilterPost::getInstance();
                    if ( $deleteDomainRequest->isComplete() ) {
                        return AutoWhitelistDomainStore::getInstance()->deleteDomain(
                            $deleteDomainRequest->getDomain(),
                            $deleteDomainRequest->getSource()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                case "update":
                    $updateDomainRequest = UpdateAutoWhitelistDomainPost::getInstance();
                    if( $updateDomainRequest->isComplete() ) {
                        return AutoWhitelistDomainStore::getInstance()->updateDomain(
                            $updateDomainRequest->getSenderDomain(),
                            $updateDomainRequest->getSrc(),
                            $updateDomainRequest->getSenderDomainId(),
                            $updateDomainRequest->getSrcId()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                default:
                    return new AjaxResult(false, AjaxResult::getUnhandledActionMsg());
            }
        case "whitelistEmailStore":

            if(!$loggedInUser->isAdmin()) {
                return new AjaxResult(false, AjaxResult::getAccessDeniedMsg());
            }

            switch ($action) {
                case "read":
                    return  WhitelistEmailStore::getInstance()->getEmails($request->getLimit(), $request->getStart(), $request->getSortProperty(), $request->getSortDirection(), $request->getFilters());
                case "addEmail":
                    $addEmailRequest = EmailFilterPost::getInstance();
                    if ( $addEmailRequest->isComplete() ) {
                        return WhitelistEmailStore::getInstance()->addEmail(
                            $addEmailRequest->getEmail()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                case "delete":
                    $deleteEmailRequest = EmailFilterPost::getInstance();
                    if ( $deleteEmailRequest->isComplete() ) {
                        return WhitelistEmailStore::getInstance()->deleteEmail(
                            $deleteEmailRequest->getEmail()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                case "update":
                    $updateEmailRequest = UpdateEmailFilterPost::getInstance();
                    if( $updateEmailRequest->isComplete() ) {
                        return WhitelistEmailStore::getInstance()->updateEmail(
                            $updateEmailRequest->getOldEmail(),
                            $updateEmailRequest->getNewEmail()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                default:
                    return new AjaxResult(false, AjaxResult::getUnhandledActionMsg());
            }
        case "whitelistDomainStore":

            if(!$loggedInUser->isAdmin()) {
                return new AjaxResult(false, AjaxResult::getAccessDeniedMsg());
            }

            switch ($action) {
                case "read":
                    return  WhitelistDomainStore::getInstance()->getDomains($request->getLimit(), $request->getStart(), $request->getSortProperty(), $request->getSortDirection(), $request->getFilters());
                case "addDomain":
                    $addDomainRequest = DomainFilterPost::getInstance();
                    if ( $addDomainRequest->isComplete() ) {
                        return WhitelistDomainStore::getInstance()->addDomain(
                            $addDomainRequest->getDomain()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                case "delete":
                    $deleteDomainRequest = DomainFilterPost::getInstance();
                    if ( $deleteDomainRequest->isComplete() ) {
                        return WhitelistDomainStore::getInstance()->deleteDomain(
                            $deleteDomainRequest->getDomain()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                case "update":
                    $updateDomainRequest = UpdateDomainFilterPost::getInstance();
                    if( $updateDomainRequest->isComplete() ) {
                        return WhitelistDomainStore::getInstance()->updateDomain(
                            $updateDomainRequest->getOldDomain(),
                            $updateDomainRequest->getNewDomain()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                default:
                    return new AjaxResult(false, AjaxResult::getUnhandledActionMsg());
            }
        case "blacklistEmailStore":

            if(!$loggedInUser->isAdmin()) {
                return new AjaxResult(false, AjaxResult::getAccessDeniedMsg());
            }

            switch ($action) {
                case "read":
                    return  BlacklistEmailStore::getInstance()->getEmails($request->getLimit(), $request->getStart(), $request->getSortProperty(), $request->getSortDirection(), $request->getFilters());
                case "addEmail":
                    $addEmailRequest = EmailFilterPost::getInstance();
                    if ( $addEmailRequest->isComplete() ) {
                        return BlacklistEmailStore::getInstance()->addEmail(
                            $addEmailRequest->getEmail()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                case "delete":
                    $deleteEmailRequest = EmailFilterPost::getInstance();
                    if ( $deleteEmailRequest->isComplete() ) {
                        return BlacklistEmailStore::getInstance()->deleteEmail(
                            $deleteEmailRequest->getEmail()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                case "update":
                    $updateEmailRequest = UpdateEmailFilterPost::getInstance();
                    if( $updateEmailRequest->isComplete() ) {
                        return BlacklistEmailStore::getInstance()->updateEmail(
                            $updateEmailRequest->getOldEmail(),
                            $updateEmailRequest->getNewEmail()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                default:
                    return new AjaxResult(false, AjaxResult::getUnhandledActionMsg());
            }
        case "blacklistDomainStore":

            if(!$loggedInUser->isAdmin()) {
                return new AjaxResult(false, AjaxResult::getAccessDeniedMsg());
            }

            switch ($action) {
                case "read":
                    return  BlacklistDomainStore::getInstance()->getDomains($request->getLimit(), $request->getStart(), $request->getSortProperty(), $request->getSortDirection(), $request->getFilters());
                case "addDomain":
                    $addEmailRequest = DomainFilterPost::getInstance();
                    if ( $addEmailRequest->isComplete() ) {
                        return BlacklistDomainStore::getInstance()->addDomain(
                            $addEmailRequest->getDomain()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                case "delete":
                    $deleteEmailRequest = DomainFilterPost::getInstance();
                    if ( $deleteEmailRequest->isComplete() ) {
                        return BlacklistDomainStore::getInstance()->deleteDomain(
                            $deleteEmailRequest->getDomain()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                case "update":
                    $updateDomainRequest = UpdateDomainFilterPost::getInstance();
                    if( $updateDomainRequest->isComplete() ) {
                        return BlacklistDomainStore::getInstance()->updateDomain(
                            $updateDomainRequest->getOldDomain(),
                            $updateDomainRequest->getNewDomain()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                default:
                    return new AjaxResult(false, AjaxResult::getUnhandledActionMsg());
            }
        case "userAdminStore":

            if(!$loggedInUser->isAdmin() && $action != "setPassword" ) {
                return new AjaxResult(false, AjaxResult::getAccessDeniedMsg());
            }

            switch ($action) {
                case "read":
                    return  UserAdminStore::getInstance()->getUsers($request->getLimit(), $request->getStart(), $request->getSortProperty(), $request->getSortDirection(), $request->getFilters());
                case "addUser":
                    $addUserRequest = CreateUserFilterPost::getInstance();
                    if ( $addUserRequest->isComplete() ) {
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
                    $deleteUserRequest = DeleteUserFilterPost::getInstance();
                    if ( $deleteUserRequest->isComplete() ) {
                        return UserAdminStore::getInstance()->deleteUser(
                            $deleteUserRequest->getUsername()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                case "setPassword":
                    $UpdateUserPasswordRequest = UpdateUserPasswordFilterPost::getInstance();
                    if($UpdateUserPasswordRequest->isComplete()) {
                        if($loggedInUser->getUsername() == $UpdateUserPasswordRequest->getUsername() || $loggedInUser->isAdmin()){
                            $userObject = User::getUserByName($UpdateUserPasswordRequest->getUsername());
                            if($userObject->isUserExisting()) {
                                $success = $userObject->setPassword($UpdateUserPasswordRequest->getPassword());
                                return new AjaxResult($success, "Tryed to set password");
                            }
                        } else {
                            return new AjaxResult(false, AjaxResult::getAccessDeniedMsg());
                        }
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                case "update":
                    $updateUserReqest = UpdateUserFilterPost::getInstance();
                    if($updateUserReqest->isComplete()) {
                        return UserAdminStore::getInstance()->updateUser($updateUserReqest->getId(), $updateUserReqest->getUsername(), $updateUserReqest->getEmail(), $updateUserReqest->isAdmin());
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                default:
                    return new AjaxResult(false, AjaxResult::getUnhandledActionMsg());
            }
        case "userAliasStore":

            if(!$loggedInUser->isAdmin()) {
                return new AjaxResult(false, AjaxResult::getAccessDeniedMsg());
            }

            switch ($action) {
                case "read":
                    return  UserAliasStore::getInstance()->getAliases($request->getLimit(), $request->getStart(), $request->getSortProperty(), $request->getSortDirection(), $request->getFilters());
                case "addAlias":
                    $addAliasRequest = CreateAliasFilterPost::getInstance();
                    if ( $addAliasRequest->isComplete() ) {
                        return UserAliasStore::getInstance()->addAlias(
                            $addAliasRequest->getUsername(),
                            $addAliasRequest->getAlias()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                case "delete":
                    $deleteAliasRequest = DeleteAliasFilterPost::getInstance();
                    if ( $deleteAliasRequest->isComplete() ) {
                        return UserAliasStore::getInstance()->deleteAlias(
                            $deleteAliasRequest->getAliasId()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                case "update":
                    $updateAliasRequest = UpdateAliasFilterPost::getInstance();
                    if( $updateAliasRequest->isComplete() ) {
                        return UserAliasStore::getInstance()->updateAlias(
                            $updateAliasRequest->getAliasId(),
                            $updateAliasRequest->getEmail(),
                            $updateAliasRequest->getUsername()
                        );
                    } else {
                        return new AjaxResult(false, AjaxResult::getIncompleteMsg());
                    }
                default:
                    return new AjaxResult(false, AjaxResult::getUnhandledActionMsg());
            }
        case "userFilterStore":

            if(!$loggedInUser->isAdmin()) {
                return new AjaxResult(false, AjaxResult::getAccessDeniedMsg());
            }

            switch ($action) {
                case "getGreylistFilter":
                    return UserAdminStore::getInstance()->getGreylistUserFilterOptions();
                case "getUserAliasFilter":
                    return UserAliasStore::getInstance()->getUserAliasFilterOptions();
                case "getUsers":
                    return UserAliasStore::getInstance()->getUserList();
                default:
                    return new AjaxResult(false, AjaxResult::getUnhandledActionMsg());
            }
        default:
            return new AjaxResult(false, AjaxResult::getUnhandledActionMsg());
    }
}