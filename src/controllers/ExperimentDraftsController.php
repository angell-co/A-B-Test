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
use craft\web\Controller;

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

        $experimentId = Craft::$app->getRequest()->getBodyParam('experimentId');
        $draftIds = Craft::$app->getRequest()->getBodyParam('draftIds');

        $db = Craft::$app->getDb();
        $transaction = $db->beginTransaction();
        try {
            ExperimentDraft::deleteAll(['experimentId' => $experimentId]);

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
     *
     */
    public function actionRemove()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $draftId = $this->request->getRequiredBodyParam('id');

        ExperimentDraft::deleteAll(['draftId' => $draftId]);

        return $this->asJson(['success' => true]);
    }
}
