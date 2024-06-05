<?php

use app\helpers\ApiHelper;
use app\services\ApiService;
use yii\helpers\Html; ?>

<!-- Page content -->
<div class="main-block-api">
    <h4>Документация по API SmartArrangement</h4>
    <p>
        API предоставляет возможность использовать функционал системы на сторонних ресурсах с любой веб-архитектурой.<br>
        Для доступа к API необходимо получить ключ доступа (TEST_API_KEY: <b><?= ApiHelper::TEST_API_KEY ?></b>)
    </p>
    <p>
        Ниже представлены все возможные запросы к API с подробным описанием параметров
    </p>
    <table class="table table-striped">
        <tr>
            <th>Тип</th>
            <th>Описание</th>
            <th>URL</th>
            <th>Параметры</th>
            <th></th>
        </tr>

        <tr>
            <td class="first-col"><span class="get-type">GET</span></td>
            <td class="second-col">Возвращает список всех МАФ, удовлетворяющих заданным параметрам</td>
            <td class="third-col">/api/get-objects</td>
            <td class="fourth-col">
                <span class="param-name">type</span>: тип объекта<br>
                <span class="param-name">minCost</span>: мин. цена<br>
                <span class="param-name">maxCost</span>: макс. цена<br>
                <span class="param-name">style</span>: стиль<br>
            </td>
            <td class="fifth-col"><?= Html::a('Протестировать', ['/sandbox/get-objects']) ?></td>
        </tr>

        <tr>
            <td class="first-col"><span class="get-type">GET</span></td>
            <td class="second-col">Возвращает список всех дворовых территорий</td>
            <td class="third-col">/api/get-territories</td>
            <td class="fourth-col">
                <i>без параметров</i>
            </td>
            <td class="fifth-col"><?= Html::a('Протестировать', ['/sandbox/get-territories']) ?></td>
        </tr>

        <tr>
            <td class="first-col"><span class="get-type">GET</span></td>
            <td class="second-col">Возвращает список весов/приоритетов по возрастным категориям и направленностям</td>
            <td class="third-col">/api/get-real-weights</td>
            <td class="fourth-col">
                <span class="param-name">tId*</span>: ID дворовой территории<br>
                <span class="param-name">agesInterval</span>: возрастной интервал<br>
            </td>
            <td class="fifth-col"><?= Html::a('Протестировать', ['/sandbox/get-real-weights']) ?></td>
        </tr>
    </table>
</div>



<style>
    table {
        margin-top: 30px;
    }

    h4 {
        margin-bottom: 30px;
    }

    .main-block-api {
        background-color: white;
        border-radius: 5px;
        margin-top: 10px;
        padding: 20px;
    }

    .first-col {
        width: 10%;
    }

    .second-col {
        width: 30%;
    }

    .third-col {
        width: 20%;
    }

    .fourth-col {
        width: 25%;
    }

    .fifth-col {
        width: 15%;
        vertical-align: middle;
        text-align: center;
    }

    .get-type {
        color: #007200;
        font-weight: bold;
    }

    .param-name {
        font-weight: 500;
    }
</style>