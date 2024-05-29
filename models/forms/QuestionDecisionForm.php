<?php

namespace app\models\forms;

use app\components\arrangement\TerritoryConcept;
use app\facades\ArrangementModelFacade;
use app\facades\TerritoryFacade;
use Yii;
use yii\base\Model;

class QuestionDecisionForm extends Model
{
    public $decision;

    /** @var ArrangementModelFacade[]  */
    public $territoires = [];

    public function rules()
    {
        return [
            [['decision'], 'required'],
            [['decision'], 'integer'],
        ];
    }

    public function generateVariants(array $votes, $territoryId)
    {
        $facade = Yii::createObject(TerritoryFacade::class);
        $this->territoires[] = $facade->generateTerritoryArrangement(TerritoryConcept::TYPE_BASE_WEIGHTS, $territoryId);
        $this->territoires[] = $facade->generateTerritoryArrangement(TerritoryConcept::TYPE_CHANGE_WEIGHTS, $territoryId);
        $this->territoires[] = $facade->generateTerritoryArrangement(TerritoryConcept::TYPE_SELF_VOTES, $territoryId, $votes);
    }
}