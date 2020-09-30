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
use Craft;
use craft\helpers\UrlHelper;
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
     * @param int|null $experimentId
     * @param Experiment|null $experiment
     * @return Response
     */
    public function actionEdit(int $experimentId = null, Experiment $experiment = null): Response
    {
        $variables = [];

        // Set up the model
        $variables['brandNewExperiment'] = false;

        if ($experimentId !== null) {
            if ($experiment === null) {
                $experiment = AbTest::$plugin->getExperiments()->getExperimentById($experimentId);

                if (!$experiment) {
                    throw new NotFoundHttpException('Experiment not found');
                }
            }

            $variables['title'] = $experiment->name;
        } else {
            if ($experiment === null) {
                $experiment = new Experiment();
                $variables['brandNewExperiment'] = true;
            }

            $variables['title'] = Craft::t('ab-test', 'Create a new experiment');
        }

        $variables['experimentId'] = $experimentId;
        $variables['experiment'] = $experiment;

        // Breadcrumbs
        $variables['crumbs'] = [
            [
                'label' => Craft::t('ab-test', 'A/B Test'),
                'url' => UrlHelper::url('ab-test')
            ],
            [
                'label' => Craft::t('ab-test', 'Experiments'),
                'url' => UrlHelper::url('ab-test/experiments')
            ]
        ];

        // Set the "Continue Editing" URL
        $variables['continueEditingUrl'] = "ab-test/experiments/{$experiment->id}";

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
