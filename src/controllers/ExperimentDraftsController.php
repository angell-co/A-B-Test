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
use angellco\abtest\models\Experiment;
use angellco\abtest\records\ExperimentDraft;
use Craft;
use craft\elements\Entry;
use craft\web\Controller;
use yii\web\Response;

/**
 * Experiment drafts controller.
 *
 * @author    Angell & Co
 * @package   AbTest
 * @since     1.0.0
 */
class ExperimentDraftsController extends Controller
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

        $experimentId = Craft::$app->getRequest()->getRequiredBodyParam('experimentId');
        $draftIds = Craft::$app->getRequest()->getRequiredBodyParam('draftIds');
        $allDraftIds = Craft::$app->getRequest()->getRequiredBodyParam('allDraftIds');

        // Delete all records for all drafts of this entry so we definitely clear them all out
        ExperimentDraft::deleteAll(['draftId' => $allDraftIds]);

        $db = Craft::$app->getDb();
        $transaction = $db->beginTransaction();
        try {

            foreach ($draftIds as $draftId) {
                $record = new ExperimentDraft();
                $record->experimentId = $experimentId;
                $record->draftId = $draftId;
                $record->save(false);
            }

            $transaction->commit();

            return $this->asJson(['success' => true]);
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $this->asErrorJson('Ooops');
    }

    /**
     * Deletes a draft / experiment relation.
     *
     * @return Response
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDelete(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $draftId = $this->request->getRequiredBodyParam('id');

        ExperimentDraft::deleteAll(['draftId' => $draftId]);

        return $this->asJson(['success' => true]);
    }
}
