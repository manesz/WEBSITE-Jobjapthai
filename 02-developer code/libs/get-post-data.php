<?php

if (!session_id())
    session_start();
global $wpdb;
if ($_REQUEST) {
    $classCandidate = new Candidate($wpdb);
    $classContact = new Contact($wpdb);
    $emailInfo = 'info@jobjapthai.com'; //email info
    $emailSale = 'sale@jobjapthai.com'; //email sale
    $sendEmailContactUs = empty($_REQUEST['send_email_contact_us']) ? false : $_REQUEST['send_email_contact_us'];
    if ($sendEmailContactUs == 'true') {

        $security_code = empty($_REQUEST['security_code']) ? '' : $_REQUEST['security_code'];
        $send_subject = empty($_REQUEST['send_subject']) ? '' : $_REQUEST['send_subject'];
        $send_name = empty($_REQUEST['send_name']) ? '' : $_REQUEST['send_name'];
        $send_subject = empty($_REQUEST['send_subject']) ? '' : $_REQUEST['send_subject'];
        if ($_SESSION['captcha_contact_us']['code'] != @$security_code) {
            echo 'error_captcha';
        } else {
            $subject = "Email Contact Us from $send_name";
            ob_start();
            require_once("content-email/content-email.php");
            $message = ob_get_contents();
            ob_end_clean();
            $title = "Contact Us From Job Jap Thai.";
            $content = $classContact->buildContentHtmlEmail($_REQUEST);
            $message = str_replace('{{title}}', $title, $message);
            $message = str_replace('{{content}}', $content, $message);
            $result = wp_mail($emailInfo, $subject, $message);
            if ($result)
                echo 'success';
            else echo 'fail';
        }
        exit;
    }

    //Employer

//    if (is_user_logged_in()) {
    $newPackage = empty($_REQUEST['new_package']) ? false : $_REQUEST['new_package'];
    $listPackage = empty($_REQUEST['list_package']) ? false : $_REQUEST['list_package'];
    $postPackage = empty($_REQUEST['post_package']) ? false : $_REQUEST['post_package'];
    $postJob = empty($_REQUEST['post_job']) ? false : $_REQUEST['post_job'];
    $classPackage = new Package($wpdb);
    if ($newPackage == 'true') {
        require_once("pages/package-new.php");
        exit;
    }
    if ($listPackage == 'true') {
        require_once("pages/package-list.php");
        exit;
    }
    if ($postPackage == 'true') {
        $postType = $_REQUEST['type_post'];
        if ($postType == 'add') {
            $result = $classPackage->addSelectPackage($_REQUEST);
            if ($result)
                echo 'success';
            else echo 'fail';
        } else if ($postType == 'edit') {
            $result = $classPackage->editSelectPackage($_REQUEST);
            if ($result)
                echo 'success';
            else echo 'fail';
        } else if ($postType == 'set_status_package') {//Cancel package
            $result = $classPackage->setStatusPackage($_REQUEST);
            if ($result)
                echo $classEmployer->returnMessage("Cancel Package Success.", false);
            else
                echo $classEmployer->returnMessage("Cancel Package Fail.", true);
        } else if ($postType == 'confirm_buy_package') {
            $employerID = $_REQUEST['employer_id'];
            $packageID = empty($_REQUEST['package_id']) ? 0 : $_REQUEST['package_id'];
            $employerData = $classEmployer->getUser($employerID);
            $emailEmployer = $employerData->user_email;
            ob_start();
            require_once("content-email/content-email.php");
            $message = ob_get_contents();
            ob_end_clean();
            $title = "Buy Package Confirmation.";
            $content = $classPackage->buildHtmlEmailBuyPackage($packageID, $employerID);
            $message = str_replace('{{title}}', $title, $message);
            $message = str_replace('{{content}}', $content, $message);

//            echo $message;
            if (!wp_mail($emailSale, "Buy Package Confirmation from Job Jap Thai", $message)) {
                echo $classCandidate->returnMessage("Sorry error send email.", true);
            }

            if (!wp_mail($emailEmployer, "Buy Package Confirmation from Job Jap Thai", $message)) {
                echo $classEmployer->returnMessage("Sorry error send email.", true);
                exit;
            }
            $result = $classPackage->setStatusPackage($_REQUEST);

            if ($result)
                echo $classEmployer->returnMessage("Send email confirm Success.", false);
            else
                echo $classEmployer->returnMessage("Update Status Fail.", true);
        } else if ($postType == 'approve_package') {
            $result = $classPackage->setStatusPackage($_REQUEST);
            if (!$result) {
                echo $classPackage->returnMessage('Approve package fail.', false);
            } else {
                echo $classPackage->returnMessage('Approve package success.', false);
            }

        } else if ($postType == 'file_package_payment') {
            $employerID = empty($_REQUEST['employer_id']) ? 0 : $_REQUEST['employer_id'];
            $package_id = empty($_REQUEST['package_id']) ? 0 : $_REQUEST['package_id'];
            $result = $classPackage->addAttachFilePayment($_FILES['payment_file'], $employerID);
            $result['msg'] = strip_tags($result['msg']);
            if (!$result['error']) {
                if ($classPackage->setPaymentFilePath($package_id, $result['path']))
                    echo $classPackage->returnMessage($result, $result['error']);
            } else {
                echo $classPackage->returnMessage($result, $result['error']);
            }
        }  else if ($postType == 'delete_select_package') {
            $result = $classPackage->setPublishSelectPackage($_REQUEST['package_id']);
            if ($result){
                echo $classPackage->returnMessage("Delete success.", false);
            }else {
                echo $classPackage->returnMessage("Delete false.", true);
            }
        }

        //Manage Package flow
        else if ($postType == 'admin_add') {
            $result = $classPackage->addPackage($_REQUEST);
            if ($result) {
                echo $classPackage->returnMessage('Add package success.', false);
            } else echo $classPackage->returnMessage('Add package fail.', true);
        } else if ($postType == 'admin_edit') {
            $result = $classPackage->editPackage($_REQUEST);
            if ($result) {
                echo $classPackage->returnMessage('Edit package success.', false);
            } else echo $classPackage->returnMessage('Edit package fail.', true);
        } else if ($postType == 'admin_delete') {
            $result = $classPackage->deletePackage($_REQUEST['package_id']);
            if ($result) {
                echo $classPackage->returnMessage('Delete package success.', false);
            } else echo $classPackage->returnMessage('Delete package fail.', true);
        }
        exit;
    }

    if ($postJob == "true") {
        $postType = empty($_REQUEST['post_type']) ? false : $_REQUEST['post_type'];
        if ($postType == 'add') {
            $result = $classEmployer->addPostJob($_REQUEST);
        } else if ($postType == 'edit') {
            $result = $classEmployer->editPostJob($_REQUEST);
        } else if ($postType == 'delete') {
            $status = empty($_REQUEST['status']) ? false : $_REQUEST['status'];
            $employerID = empty($_REQUEST['employer_id']) ? 0 : $_REQUEST['employer_id'];
            $result = $classEmployer->deletePosJob($_REQUEST['post_id'], $status, $employerID);
        } else if ($postType == 'load_edit') {
            $getPostID = empty($_REQUEST['post_id']) ? 0 : $_REQUEST['post_id'];
            $employerID = empty($_REQUEST['employer_id']) ? 0 : $_REQUEST['employer_id'];
            $result = $classEmployer->buildFormPostJob($getPostID, $employerID);
        } else if ($postType == 'feature_image') {
            if ($_REQUEST['post_id'] == 0) {
                $post_id = $classEmployer->addPostJob(array(), 'draft');
            } else {
                $post_id = $_REQUEST['post_id'];
                $classEmployer->deleteOldThumbnail($post_id);
            }
            $result = $classEmployer->uploadImage($_FILES['feature_image']);
            if (!$result['error']) {
                if ($classEmployer->setFeatureImage($post_id, $result['url'])) {
                    $result['post_id'] = $post_id;
                }
            }
            $result = $classEmployer->returnMessage($result, $result['error']);
        } else if ($postType == 'delete_feature') {
            $result = $classEmployer->deleteOldThumbnail($post_id);
            $message = $result ? "Delete success." : "Delete fail.";
            echo $classEmployer->returnMessage($message, !$result);
            exit;
        } else if ($postType == 'get_total_package') {
            echo $classPackage->buildTotalPackage($_REQUEST['user_id']);
            exit;
        }
        echo $result;
        exit;
    }

    $employerPost = empty($_REQUEST['employer_post']) ? false : $_REQUEST['employer_post'];
    if ($employerPost == 'true') {
        $classEmployer = new Employer($wpdb);
        $postType = empty($_REQUEST['post_type']) ? false : $_REQUEST['post_type'];
        $getPostBackend = empty($_REQUEST['post_backend']) ? false : $_REQUEST['post_backend'];
        $employerID = empty($_REQUEST['employer_id']) ? 0 : $_REQUEST['employer_id'];
        if ($postType == 'add') {
            $result = $classEmployer->employerRegister($_REQUEST);
            if (!$result['error'] && !$getPostBackend) {
                //$this->setUserLogin($user_id);
                $_REQUEST['key'] = $result['key'];
                ob_start();
                require_once("content-email/content-email.php");
                $message = ob_get_contents();
                ob_end_clean();
                $title = "Register Confirmation.";
                $content = $classEmployer->buildContentHtmlConfirm($result['key']);
                $message = str_replace('{{title}}', $title, $message);
                $message = str_replace('{{content}}', $content, $message);

                if (!wp_mail($_REQUEST['employerEmail'], "Register Confirmation from Job Jap Thai", $message)) {
                    echo $classEmployer->returnMessage("Sorry error send email.", true);
                }
            }
            echo $classEmployer->returnMessage($result, $result['error'], true);
        } else if ($postType == 'edit') {
            echo $classEmployer->editEmployer($_REQUEST);
        } else if ($postType == 'logo_image') {
            $result = $classEmployer->uploadLogoImage($_FILES['logo_image'], 700);
            if (!$result['error']) {
                if (!$classEmployer->deleteOldLogo($employerID)) {
                    echo $classEmployer->returnMessage("Error delete old image.", true);
                    exit;
                }
                $classEmployer->setLogoPath($employerID, $result['path']);
            }
            echo $classEmployer->returnMessage($result, $result['error']);
        } else if ($postType == 'delete_avatar') {
            $result = $classEmployer->deleteOldLogo($employerID);
            if ($result)
                $classEmployer->setBannerPath($employerID, false);
            $message = $result ? "Delete success." : "Delete fail.";
            echo $classEmployer->returnMessage($message, !$result);
        } else if ($postType == 'image_banner') {
            $result = $classEmployer->uploadBannerImage($_FILES['image_banner'], $employerID, 700);
            if (!$result['error']) {
                if (!$classEmployer->deleteOldBanner($employerID)) {
                    echo $classEmployer->returnMessage("Error delete old image.", true);
                    exit;
                }
                $classEmployer->setBannerPath($employerID, $result['path']);
            }
            echo $classEmployer->returnMessage($result, $result['error']);
        } else if ($postType == 'delete_banner') {
            $result = $classEmployer->deleteOldBanner($employerID);
            if ($result)
                $classEmployer->setBannerPath($employerID, false);
            $message = $result ? "Delete success." : "Delete fail.";
            echo $classEmployer->returnMessage($message, !$result);
        } else if ($postType == 'search_candidate') {
            $result = $classEmployer->searchCandidate($_REQUEST);
            echo $result;
        } else if ($postType == 'request_profile') {
            $result = $classEmployer->addRequestProfile($_REQUEST['candidate_id'], $_REQUEST['employer_id']);
            if ($result)
                echo $classEmployer->returnMessage('Send request profile success.', false);
            else
                echo $classEmployer->returnMessage('Send request profile fail.', true);
        } else if ($postType == 'view_candidate') {
            $result = $classCandidate->buildViewCandidateProfile($_REQUEST['candidate_id']);
            echo $result;
        } else if ($postType == 'set_package_for_job') {
            $result = $classPackage->setPackageForJob($_REQUEST['post_id']);
            echo $result;
        }
        exit;
    }
    //End Employer

    //Pre register
    $preRegister = empty($_REQUEST['pre_register']) ? false : $_REQUEST['pre_register'];
    if ($preRegister) {
        $result = $classCandidate->addPreRegister($_REQUEST);
        echo strip_tags($classCandidate->returnMessage($result, $result['error'], true));
        //$_SESSION['fci'] = 1;
        exit;
    }
    //Candidate
    $candidatePost = empty($_REQUEST['candidate_post']) ? false : $_REQUEST['candidate_post'];
    if ($candidatePost == 'true') {
        $postType = empty($_REQUEST['post_type']) ? false : $_REQUEST['post_type'];
        $getPostBackend = empty($_REQUEST['post_backend']) ? false : $_REQUEST['post_backend'];
        $candidateID = empty($_REQUEST['candidate_id']) ? 0 : $_REQUEST['candidate_id'];
        switch ($postType) {
            case "register":
                $result = $classCandidate->addCandidate($_REQUEST);

                $candidateID = $result['candidate_id'];
                /*if (!$result['error'] && !$getPostBackend) {
                    //$this->setUserLogin($user_id);
                    $_REQUEST['key'] = $result['key'];
                    ob_start();
                    require_once("content-email/register_confirmation.php");
                    $message = ob_get_contents();
                    ob_end_clean();
                    $title = "Register Confirmation.";
                    $content = $classEmployer->buildContentHtmlConfirm($_REQUEST['key']);
                    $message = str_replace('{{title}}', $title, $message);
                    $message = str_replace('{{content}}', $content, $message);

                    if (!wp_mail($_REQUEST['email'], "Register Confirmation from Job Jap Thai", $message)) {
                        echo $classCandidate->returnMessage("Sorry error send email.", true);
                    }
                }*/
                if (!$result['error']) {//add resume file
                    $resultAddResumeFile = $classCandidate->addAttachResume($_FILES['attach_resume'], $candidateID);
                    if (!$resultAddResumeFile['error']) {
                        $classCandidate->setAttachResumePath($candidateID, $resultAddResumeFile['path']);
                    } else {
                        $classCandidate->returnMessage($resultAddResumeFile, $resultAddResumeFile['error'], true);
                        exit;
                    }
                }
                echo $classCandidate->returnMessage($result, $result['error'], true);
                break;
            case "none_member":
                $result = $classCandidate->addNoneMember($_REQUEST);
                if (!$result['error']) {
                    $candidateID = $result['candidate_id'];
                    $jobID = $_REQUEST['job_id'];

                    $resultAddResumeFile = $classCandidate->addAttachResume($_FILES['attach_resume'], $candidateID);
                    if (!$resultAddResumeFile['error']) {
                        $classCandidate->setAttachResumePath($candidateID, $resultAddResumeFile['path']);

                        $classApply = new Apply($wpdb);
                        $checkJobApply = $classApply->checkJobIsApply($candidateID, $jobID);
                        if (!$checkJobApply) {
                            $resultApply = $classApply->addApplyJob(
                                $candidateID,
                                $jobID,
                                $_REQUEST['employer_id'],
                                false
                            );
                            if ($resultApply['error']) {
                                echo $classCandidate->returnMessage($resultApply, $resultApply['error']);
                                exit;
                            }
                        }
                    }
                }
                echo $classCandidate->returnMessage($result, $result['error'], true);
                exit;
                break;
            case "edit":
                break;
            case "send_email_confirm":
                ob_start();
                require_once("content-email/content-email.php");
                $message = ob_get_contents();
                ob_end_clean();
                $title = "Register Confirmation.";
                $content = $classEmployer->buildContentHtmlConfirm($_REQUEST['key']);
                $message = str_replace('{{title}}', $title, $message);
                $message = str_replace('{{content}}', $content, $message);
                $showStyle = empty($_REQUEST['show_style']) ? true : false;
                if (!wp_mail($_REQUEST['email'], "Register Confirmation from Job Jap Thai", $message)) {
                    echo "Sorry error send email.";
                } else
                    echo "Send Success.";
                exit;
                break;
            case "get_career_profile":
                $result = $classCandidate->buildCareerProfileTable($candidateID);
                echo $result;
                break;
            case "get_desired_job":
                $result = $classCandidate->buildDesiredJobTable($candidateID);
                echo $result;
                break;
            case "get_education":
                $result = $classCandidate->buildEducationTable($candidateID);
                echo $result;
                break;
            case "get_work_experience":
                $result = $classCandidate->buildWorkExperienceTable($candidateID);
                echo $result;
                break;
            case "add_career_profile":
                $result = $classCandidate->addCareerProfile($_REQUEST);
                if ($result)
                    echo $classCandidate->returnMessage("Add career profile success.", false);
                else echo $classCandidate->returnMessage("Add career profile fail.", true);
                break;
            case "add_desired_job":
                $result = $classCandidate->addDesiredJob($_REQUEST);
                if ($result)
                    echo $classCandidate->returnMessage("Add desired job success.", false);
                else echo $classCandidate->returnMessage("Add desired job fail.", true);
                break;
            case "add_education":
                $result = $classCandidate->addEducation($_REQUEST);
                if ($result)
                    echo $classCandidate->returnMessage("Add education success.", false);
                else echo $classCandidate->returnMessage("Add education fail.", true);
                break;
            case "add_work_experience":
                $result = $classCandidate->addWorkExperience($_REQUEST);
                if ($result)
                    echo $classCandidate->returnMessage("Add work experience success.", false);
                else echo $classCandidate->returnMessage("Add work experience fail.", true);
                break;

            //Edit
            case "edit_information":
                $result = $classCandidate->editInformation($_REQUEST);
                echo $result;
                break;
            case "edit_career_profile":
                $result = $classCandidate->editCareerProfile($_REQUEST);
                echo $result;
                break;
            case "edit_desired_job":
                $result = $classCandidate->editDesiredJob($_REQUEST);
                echo $result;
                break;
            case "edit_education":
                $result = $classCandidate->editEducation($_REQUEST);
                echo $result;
                break;
            case "edit_work_experience":
                $result = $classCandidate->editWorkExperience($_REQUEST);
                echo $result;
                break;
            case "edit_skill_languages"://var_dump($_REQUEST);exit;
                $result = $classCandidate->editSkillLanguages($_REQUEST);
                echo $result;
                break;
            case "image_avatar":
                $result = $classCandidate->uploadAvatarImage($_FILES['image_avatar']);
                if (!$result['error']) {
                    if (!$classCandidate->deleteOldAvatar($candidateID)) {
                        echo $classCandidate->returnMessage("Error delete old image.", true);
                        exit;
                    }
                    $classCandidate->setAvatarPath($candidateID, $result['path']);
                }
                echo $classCandidate->returnMessage($result, $result['error']);
                break;
            case "approve_request_profile":
                $result = $classCandidate->setApproveRequestProfile($_REQUEST['request_id']);
                echo $result;
                break;
            //End edit

            //Delete
            case "delete_career_profile":
                $result = $classCandidate->deleteCareerProfile($_REQUEST);
                echo $result;
                break;
            case "delete_desired_job":
                $result = $classCandidate->deleteDesiredJob($_REQUEST);
                echo $result;
                break;
            case "delete_education":
                $result = $classCandidate->deleteEducation($_REQUEST);
                echo $result;
                break;
            case "delete_work_experience":
                $result = $classCandidate->deleteWorkExperience($_REQUEST);
                echo $result;
                break;
            case 'delete_avatar': {
                $result = $classCandidate->deleteOldAvatar($candidateID);
                if ($result)
                    $classCandidate->setAvatarPath($candidateID, false);
                $message = $result ? "Delete success." : "Delete fail.";
                echo $classCandidate->returnMessage($message, !$result);
            }
                break;
            //End Delete
        }
        exit;
    }
    // END Candidate

    //Favorite
    $favorite = empty($_REQUEST['favorite']) ? false : $_REQUEST['favorite'];
    if ($favorite == 'true') {
        $classFavorite = new Favorite($wpdb);
        if ($_REQUEST['favorite_type'] == 'job') {
            if ($_REQUEST['is_favorite'] == 'true') {
                $result = $classFavorite->addFavJob($_REQUEST['user_id'], $_REQUEST['id'], $_REQUEST['employer_id']);
                echo $result;
            } else {
                $result = $classFavorite->setPublishJob($_REQUEST['fav_id']);
                echo $result;
            }
        } else if ($_REQUEST['favorite_type'] == 'company') {
            if ($_REQUEST['is_favorite'] == 'true') {
                $result = $classFavorite->addFavCompany($_REQUEST['user_id'], $_REQUEST['id']);
                echo $result;
            } else {
                $result = $classFavorite->setPublishCompany($_REQUEST['fav_id']);
                echo $result;
            }
        }
        exit;
    }
    //End Favorite


    //Apply
    $applyPost = empty($_REQUEST['apply_post']) ? false : $_REQUEST['apply_post'];
    if ($applyPost == 'true') {
        $classApply = new Apply($wpdb);
        if ($_REQUEST['apply_type'] == 'job') {
            if ($_REQUEST['is_apply'] == 'true') {
                $result = $classApply->addApplyJob($_REQUEST['user_id'], $_REQUEST['id'], $_REQUEST['employer_id']);
                echo $result;
            } else {
                $result = $classApply->setPublishJob($_REQUEST['apply_id']);
                echo $result;
            }
        } else if ($_REQUEST['apply_type'] == 'company') {
            if ($_REQUEST['is_apply'] == 'true') {
                $result = $classApply->addApplyCompany($_REQUEST['user_id'], $_REQUEST['id']);
                echo $result;
            } else {
                $result = $classApply->setPublishCompany($_REQUEST['apply_id']);
                echo $result;
            }
        }
        exit;
    }
    //End Apply


    $query_list_job_post = empty($_REQUEST['query_list_job_post']) ? false : $_REQUEST['query_list_job_post'];
    if ($query_list_job_post == 'true') {
        $classQueryPostJob = new QueryPostJob($wpdb);
        $getType = $_REQUEST['type'];
        switch ($getType) {
            case "favorite":
                $argc = $classQueryPostJob->queryFavoriteJob($_REQUEST['user_id']);
                break;
            case "apply":
                $argc = $classQueryPostJob->queryApplyJob($_REQUEST['user_id']);
                break;
            case "search":
                $argc = $classQueryPostJob->querySearchJob();
                break;
            case "highlight_jobs":
                $argc = $classQueryPostJob->queryHighlightJobs();
                break;
            case "post_job":
                $argc = $classQueryPostJob->queryPostJob($_REQUEST['user_id']);
                break;
            case "job_update":
                $titlePage = "News jobs update";

                $cat = empty($_GET['cat']) ? false : $_GET['cat'];
                $current_cat_id = get_query_var('cat');
                if ($cat) {
                    $getTerm = get_term_by('slug', $cat, 'custom_job_cat');
                    $titlePage = $getTerm->name;
                } else if ($current_cat_id) {
                    $titlePage = get_cat_name($current_cat_id);
                }
                $argc = $classQueryPostJob->queryJobUpdate($cat, $current_cat_id);
                break;
        }
        echo $classQueryPostJob->buildListJob($argc);
        exit;
    }


    //Forget pass
    if (isset($_REQUEST['reset_pass'])) {

        $classAuthentication = new Authentication($wpdb);
        $result = $classAuthentication->checkUserForForgetPassWord($_REQUEST['user_login']);
        if ($result['error']) {
            echo $classAuthentication->returnMessage($result['msg'], true);
        } else {
            $_REQUEST['user_data'] = $result['user_data'];
            $user_data = $result['user_data'];
            $email = $user_data->user_email;

            $random_password = wp_generate_password(12, false);
            $update_user = wp_update_user(array(
                    'ID' => $user_data->ID,
                    'user_pass' => $random_password
                )
            );
            if (!$update_user) {
                echo $this->returnMessage('Oops something went wrong updating your account.', true);
            } else {
                $_REQUEST['user_login'] = $user_data->user_login;
                $_REQUEST['new_pass'] = $random_password;
                function wp_mail_set_content_type()
                {
                    return "text/html";
                }

                add_filter('wp_mail_content_type', 'wp_mail_set_content_type');
                ob_start();
                require_once("content-email/content-email.php");
                $message = ob_get_contents();
                ob_end_clean();
                $title = "Forget Your Password.";
                $content = $classAuthentication->buildContentHtmlForgetPassword();
                $message = str_replace('{{title}}', $title, $message);
                $message = str_replace('{{content}}', $content, $message);
                if (!wp_mail($email, "Forget password from Job Jap Thai", $message)) {
                    echo $classAuthentication->returnMessage("Sorry error send email.", true);
                } else {
                    echo $classAuthentication->returnMessage("Check your email address for you new password.", false);
                }
            }
        }
        exit;
    }
    //End Forget pass

//    }

    $signInPost = isset($_REQUEST['sign_in_post']) ? $_REQUEST['sign_in_post'] : false;
    if ($signInPost == "true") {
        $classAuthentication = new Authentication($wpdb);
        echo $classAuthentication->signin($_REQUEST);
        exit;
    }

    //Save other settings

    if (isset($_REQUEST['other_setting_post'])) {
        $classOthSetting = new OtherSetting($wpdb);
        $result = $classOthSetting->saveData($classOthSetting->nameTitle, $_REQUEST[$classOthSetting->nameTitle]);
        if (!$result) {
            echo json_encode(array('error' => true, 'message' => 'Save error'));
            exit;
        }
        $result = $classOthSetting->saveData($classOthSetting->nameWorkingDay, $_REQUEST[$classOthSetting->nameWorkingDay]);
        if (!$result) {
            echo json_encode(array('error' => true, 'message' => 'Save error'));
            exit;
        }
        $result = $classOthSetting->saveData($classOthSetting->namePositionList, $_REQUEST[$classOthSetting->namePositionList]);
        if (!$result) {
            echo json_encode(array('error' => true, 'message' => 'Save error'));
            exit;
        }
        $result = $classOthSetting->saveData($classOthSetting->nameJobLocation, $_REQUEST[$classOthSetting->nameJobLocation]);
        if (!$result) {
            echo json_encode(array('error' => true, 'message' => 'Save error'));
            exit;
        }

        echo json_encode(array('error' => false, 'message' => 'Save success'));
        exit;
    }

    $queryBackendPost = empty($_REQUEST['query_backend_post']) ? false : $_REQUEST['query_backend_post'];
    if ($queryBackendPost) {
        $sql = empty($_REQUEST['query_txt']) ? null : $_REQUEST['query_txt'];
        if (strpos($sql, 'select') === false) {
            $result = $wpdb->query($sql);
            if ($result)
                echo "Query Success";
            else echo "Query Fail";
        } else {
            $result = $wpdb->get_results($sql);
            if ($result)
                var_dump($result);
            else echo 'No data';
        }
        exit;
    }

    //End Save other settings
}