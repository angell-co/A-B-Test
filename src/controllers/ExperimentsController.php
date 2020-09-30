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
use Craft;
use craft\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * Experiments controller.
 *
 * @author    Angell & Co
 * @package   AbTest
 * @since     1.0.0
 */
class ExperimentsController extends Controller
{

    // Public Methods
    // =========================================================================

    /**
     * List experiments.
     *
     * @return Response
     * @throws ForbiddenHttpException
     */
    public function actionIndex(): Response
    {
        $experiments = AbTest::$plugin->getExperiments()->getAllExperiments();

        return $this->renderTemplate('ab-test/experiments/_index', [
            'experiments' => $experiments
        ]);
    }

    /**
     * Edit experiments.
     *
     * @return Response
     * @throws ForbiddenHttpException
     */
    public function actionEdit(): Response
    {
        $variables = [];
        return $this->renderTemplate('ab-test/experiments/_edit', $variables);
    }

    /**
     * Deletes an experiment.
     *
     * @return Response
     */
    public function actionDelete(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $experimentId = $this->request->getRequiredBodyParam('id');

        AbTest::$plugin->getExperiments()->deleteExperimentById($experimentId);

        return $this->asJson(['success' => true]);
    }

}
