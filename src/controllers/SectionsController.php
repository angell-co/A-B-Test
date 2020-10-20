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

        // Deal with no drafts
        if (empty($section->draftIds)) {
            // If we already have a section, remove it
            if ($section->id) {
                if (AbTest::$plugin->getSections()->deleteSectionById($section->id)) {
                    return $this->asJson([
                        'success' => true,
                    ]);
                }

                return $this->asErrorJson('Couldn’t remove all drafts.');
            }

            // If not, just return a success response with no section created
            return $this->asJson([
                'success' => true,
            ]);
        }

        // If we got this far, we have drafts and can save the section
        if (AbTest::$plugin->getSections()->saveSection($section)) {
            return $this->asJson([
                'success' => true,
                'section' => $section->toArray(['*'], ['drafts'])
            ]);
        }

        return $this->asErrorJson('Couldn’t save section.');
    }
}
