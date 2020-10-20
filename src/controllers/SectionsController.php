<?php
/**
 * A/B Test  plugin for Craft CMS 3.x
 *
 * Run A/B tests easily in Craft.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\abtest\controllers;

use angellco\abtest\AbTest;
use angellco\abtest\models\Section;
use angellco\abtest\records\SectionDraft;
use Craft;
use craft\web\Controller;
use yii\web\Response;

/**
 * Sections controller.
 *
 * @author    Angell & Co
 * @package   AbTest
 * @since     1.0.0
 */
class SectionsController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * @return \yii\web\Response
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSave()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $section = new Section();

        $section->id = Craft::$app->getRequest()->getBodyParam('sectionId');
        $section->sourceId = Craft::$app->getRequest()->getRequiredBodyParam('sourceId');
        $section->experimentId = Craft::$app->getRequest()->getRequiredBodyParam('experimentId');
        $section->draftIds = Craft::$app->getRequest()->getRequiredBodyParam('draftIds');

        // Save it
        if (AbTest::$plugin->getSections()->saveSection($section)) {
            return $this->asJson([
                'success' => true,
                'section' => $section->toArray(['*'], ['drafts'])
            ]);
        }

        return $this->asErrorJson('Couldn’t save section.');
    }

    /**
     * Deletes a draft / experiment relation.
     *
     * @return Response
     * @throws \yii\web\BadRequestHttpException
     */
//    public function actionDelete(): Response
//    {
//        $this->requirePostRequest();
//        $this->requireAcceptsJson();
//
//        $draftId = $this->request->getRequiredBodyParam('id');
//
//        ExperimentDraft::deleteAll(['draftId' => $draftId]);
//
//        return $this->asJson(['success' => true]);
//    }
}
