<?php

namespace app\controllers\backend;

use app\components\arrangement\TemplatesManager;
use app\components\arrangement\TerritoryConcept;
use app\components\coordinates\LocalCoordinatesManager;
use app\facades\TerritoryFacade;
use app\models\forms\demo\GenerateByParamsForm;
use app\models\ObjectExtended;
use app\models\work\ObjectWork;
use app\models\work\TerritoryWork;
use app\models\search\SearchTerritoryWork;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * TerritoryController implements the CRUD actions for TerritoryWork model.
 */
class DemoController extends Controller
{
    private TemplatesManager $template;
    private TerritoryFacade $facade;

    public function __construct($id, $module, TemplatesManager $template, TerritoryFacade $facade, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->template = $template;
        $this->facade = $facade;
    }

    public function actionGenerateTemplateAjax($tId)
    {
        $this->facade->generateTerritoryArrangement(TerritoryConcept::TYPE_BASE_WEIGHTS, 1, TerritoryFacade::OPTIONS_DEFAULT, $tId);
        $matrix = $this->facade->getRawMatrix();
        $objectsList = $this->facade->model->objectsPosition;
        $resultObjList = [];
        $maxHeight = 0;

        $territory = TerritoryWork::find()->where(['id' => 1])->one();

        foreach ($objectsList as $objectExt) {
            /** @var ObjectExtended $objectExt */
            if ($maxHeight < $objectExt->object->height) {
                $maxHeight = $objectExt->object->height;
            }

            $resultObjList[] = [
                'id' => $objectExt->object->id,
                'height' => ObjectWork::convertDistanceToCells($objectExt->object->height, TerritoryConcept::STEP),
                'width' => ObjectWork::convertDistanceToCells($objectExt->object->width, TerritoryConcept::STEP),
                'length' => ObjectWork::convertDistanceToCells($objectExt->object->length, TerritoryConcept::STEP),
                'rotate' => $objectExt->positionType == TerritoryConcept::HORIZONTAL_POSITION ? TerritoryConcept::HORIZONTAL_POSITION : 90,
                'link' => $objectExt->object->model_path,
                'dotCenter' => [
                    'x' => LocalCoordinatesManager::calculateLocalCoordinates($territory, $objectExt)['x'],
                    'y' => LocalCoordinatesManager::calculateLocalCoordinates($territory, $objectExt)['y'],
                ],
            ];
        }

        return json_encode(
            [
                'result' => [
                    'matrixCount' => [
                        'width' => count($matrix[0]),
                        'height' => count($matrix),
                        'maxHeight' => ObjectWork::convertDistanceToCells($maxHeight, TerritoryConcept::STEP),
                    ],
                    'matrix' => $matrix,
                    'objects' => $resultObjList,
                ],
                'analytic' => [
                    'data' => $this->facade->getAnalyticData(),
                ],
            ],
        );
    }

    public function actionGenerate()
    {
        $model = new GenerateByParamsForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $this->facade->generateTerritoryArrangement(
                TerritoryConcept::TYPE_SELF_VOTES,
                1,
                $model->addGenerateType,
                null,
                [
                    'votes' =>
                    [
                        ObjectWork::TYPE_RECREATION => $model->recreation * 10,
                        ObjectWork::TYPE_SPORT => $model->sport * 10,
                        ObjectWork::TYPE_EDUCATION => $model->education * 10,
                        ObjectWork::TYPE_GAME => $model->game * 10,
                    ]
                ]
            );
            return $this->render('generate', [
                'model' => $model,
                'data' => $this->facade->model->getDataForScene(1),
            ]);

        }

        return $this->render('generate', [
            'model' => $model,
        ]);
    }
}
