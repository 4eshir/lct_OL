<?php

/** @var $model ConstructorTerritoryForm */

use app\models\forms\ConstructorTerritoryForm;
use app\models\work\ObjectWork;

?>

<style>
    #scene-container {
        height: 600px;
    }
    #scene-container canvas {
        border-radius: 15px;
    }
</style>

<div>
    <h3>Настроения жителей</h3>
    <span><b>R:</b> <?= $model->averageMindset[ObjectWork::TYPE_RECREATION] ?>% </span>
    <span><b>S:</b> <?= $model->averageMindset[ObjectWork::TYPE_SPORT] ?>% </span>
    <span><b>E:</b> <?= $model->averageMindset[ObjectWork::TYPE_EDUCATION] ?>% </span>
    <span><b>G:</b> <?= $model->averageMindset[ObjectWork::TYPE_GAME] ?>% </span>
</div>

<div>
    <!--<h3>Жители чаще всего выбирают примерно такую планировку:</h3>
    <span><?php /*var_dump($model->generatePriorityArrangement()->getRawMatrix()) */?></span>
    <h2>Бюджет на данную планировку: </h2>
    <span><?php /*= $model->generatePriorityArrangement()->calculateBudget(); */?>₽</span>
    <h2>Время изготовления/установки объектов: </h2>
    <span><?php /*= $model->generatePriorityArrangement()->calculateCreatedTime(); */?>д. / <?php /*= $model->generatePriorityArrangement()->calculateInstallationTime(); */?>д.</span>-->
</div>

<div id="scene-container"></div>
<div id="anal-block"></div>

<script>
    var gridSizeX = '<?php echo json_decode($model->getSize(), true)['width'];?>';
    var gridSizeY = '<?php echo json_decode($model->getSize(), true)['length'];?>';
</script>

<script src="https://cdn.jsdelivr.net/npm/three@0.130.1/build/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.130.1/examples/js/loaders/GLTFLoader.js"></script>
<script>
    // Создание сцены
    const scene = new THREE.Scene();
    scene.background = new THREE.Color('#F0F8FF');
    const sceneContainer = document.getElementById('scene-container');

    const camera = new THREE.PerspectiveCamera( 75, sceneContainer.clientWidth / sceneContainer.clientHeight, 1, 1000 );
    camera.position.z = 10;
    camera.position.y = -5;
    camera.rotation.x = 0.5;

    const renderer = new THREE.WebGLRenderer();
    renderer.setSize(sceneContainer.clientWidth, sceneContainer.clientHeight);
    sceneContainer.appendChild(renderer.domElement);

    //-----------------------------------------------

    const drift = 0.5;

    // Создаем материал для ячеек сетки
    /*var gridSizeX = 10;
    var gridSizeY = 10;*/

    // Создаем сетку
    var gridGeometry = new THREE.PlaneBufferGeometry(1, 1);
    var gridMesh = new THREE.Group();

    var gridColor = new THREE.Color('#808080'); // Серый цвет

    var edgesMaterial = new THREE.LineBasicMaterial({ color: 0x000000 }); // Черный цвет для границ
    var driftCellX = gridSizeX % 2 == 0 ? 0 : drift;
    var driftCellY = gridSizeY % 2 == 0 ? 0 : drift;

    for (var i = 0; i < gridSizeX * gridSizeY; i++) {
        var cellGeometry = new THREE.BoxBufferGeometry(1, 1, 0.01);
        var cellMaterial = new THREE.MeshBasicMaterial({ color: gridColor, transparent: true, opacity: 0.5, side: THREE.DoubleSide }); // Один цвет и полупрозрачность
        var cell = new THREE.Mesh(cellGeometry, cellMaterial);
        var edges = new THREE.LineSegments(new THREE.EdgesGeometry(cellGeometry), edgesMaterial);
        cell.position.set(i % gridSizeX - gridSizeX / 2 + driftCellX, Math.floor(i / gridSizeX) - gridSizeY / 2 + driftCellY, 0);
        gridMesh.add(cell);
        cell.add(edges); // Добавляем границы к ячейке
    }

    // Добавили сетку на сцену
    scene.add(gridMesh);

    // Тестовые объекты для отладки
    //-----------------------------------------------
    /*const geometry = new THREE.BoxGeometry(1, 1, 1);
    const material = new THREE.MeshBasicMaterial( { color: 0x00ff00 } );
    const cube = new THREE.Mesh( geometry, material );
    cube.position.set(0, 0, 0.5);
    scene.add(cube);*/

    function addObject(idObject)
    {
        const loader = new THREE.GLTFLoader();
        //var rotation = dateObj.result.objects[index].rotate === 0 ? 0 : Math.PI / 2;
        //var rotateX = (dateObj.result.objects[index].length % 2 === 0) ? drift : 0;
        //var rotateY = (dateObj.result.objects[index].width % 2 === 0) ? drift : 0;
        var rotation = 0;
        var rotateX = 0;
        var rotateY = 0;

        if (rotation !== 0) {
            var temp = rotateX;
            rotateX = rotateY;
            rotateY = temp;
        }

        const randomColor = Math.floor(Math.random() * 16777215).toString(16);
        var material = new THREE.MeshBasicMaterial({color: parseInt(randomColor, 16)});
        var link = '';

        if (!link)
        {
            link = 'models/educational/Маятник Ньютона с подложкой.glb';
        }

        loader.load(
            link,
            function (gltf) {
                const model = gltf.scene;
                // Найдем все материалы модели и установим для них текстуры
                model.traverse((child) => {
                    if (child.isMesh) {
                        if (child.material.map)
                        {
                            material = new THREE.MeshBasicMaterial({ map: child.material.map });
                        }
                        child.material = material;
                    }
                });
                model.scale.set(1, 1, 1);
                model.position.set(0, 0, 0);

                // Добавляем модель в сцену
                scene.add(model);
                objectsToRemove.push(model);
            },
            undefined,
            function (error) {
                // Если модель отсутствует, то заменяем её на примитивный полигон (параллелепипед)
                const geometry = new THREE.BoxGeometry(1, 1, 1);
                const oneObject = new THREE.Mesh(geometry, material);

                oneObject.position.set(0 + rotateX, 0 + rotateY, 0.5);
                oneObject.rotation.z = rotation;
                scene.add(oneObject);
                objectsToRemove.push(oneObject);
                console.error('Error loading 3D model', error);
            }
        );
    }

    var rectangleGeometry = new THREE.BoxGeometry(2, 2, 1);
    var rectangleMaterial = new THREE.MeshBasicMaterial({ transparent: true, opacity: 0.8, color: 0x0000ff });
    var rectangle = new THREE.Mesh(rectangleGeometry, rectangleMaterial);
    rectangle.position.set(3, 0, 0.5);
    scene.add(rectangle);

    var sphereGeometry = new THREE.BoxGeometry(2, 3, 1)
    var sphereMaterial = new THREE.MeshBasicMaterial({ transparent: true, opacity: 0.8, color: 0xff0000, side: THREE.DoubleSide });
    var sphere = new THREE.Mesh(sphereGeometry, sphereMaterial);
    sphere.position.set(-3, 0, 0.5);
    scene.add(sphere);

    // Основные механики
    //--------------------------------

    var dot = {
        x: 'undefined',
        y: 'undefined',
        addDot: function (x, y) {
            this.x = x;
            this.y = y;
        },
        isIntegerCoordinate: function () {
            return Number.isInteger(this.x) && Number.isInteger(this.y);
        },
        clearDot: function () {
            this.x = 'undefined';
            this.y = 'undefined';
        },
        isEmpty: function () {
            return this.x === 'undefined' || this.y === 'undefined';
        }
    }

    function isEqualsDots(anotherDot, otherDot) {
        if (!anotherDot || !otherDot)
            return false;
        return anotherDot.x == otherDot.x && anotherDot.y == otherDot.y;
    }

    // Массив разрешенных к взаимодействию объектов
    var interactiveObjects = [rectangle, sphere];

    // Переменные для отслеживания перемещения объекта
    var isDragging = false;
    var selectedObject = null;
    var axisZ = 2;   // Высота на которую будем поднимать объекты при перемещении
    var offset = new THREE.Vector3();

    var outlineMeshSelectedObject = null;
    var outlineMeshSelectedObjectHover = null;

    var selectedObjectRotateX = false;
    var selectedObjectRotateY = false;
    let selectedObjectRotatePoint = {
        point0deg: Object.create(dot),
        point90deg: Object.create(dot),
        isEmptyPoint: function () {
            return this.point0deg.isEmpty() || this.point90deg.isEmpty();
        },
        clear: function () {
            this.point0deg.clearDot();
            this.point90deg.clearDot();
        },
        addPoint0deg: function (x, y) {
            this.point0deg.addDot(x, y);
        },
        addPoint90deg: function (x, y) {
            this.point90deg.addDot(x, y);
        },
        getPoint: function () {
            if (isRotation())
            {
                return this.point90deg;
            }

            return this.point0deg;
        }
    };
    var blockObjectSelection = null;
    var intersectionPoint = {x: 0, y: 0};

    // переменная для отслеживания поворота камеры
    var isRotateCamera = false;
    var degreeCamera = 0;
    var previousMouseX = 0;
    var previousMouseY = 0;

    // Направление по оси OX
    function directionX(event)
    {
        var currentMouseX = event.clientX;
        var direction = 1;

        if (currentMouseX < previousMouseX) {
            direction *=  -1;
        }

        previousMouseX = currentMouseX;
        return direction;
    }

    // Направление по оси OY
    function directionY(event)
    {
        var currentMouseY = event.clientY;
        var direction = 1;

        if (currentMouseY > previousMouseY) {
            direction *= -1;
        }

        previousMouseY = currentMouseY;
        return direction;
    }

    // Обновляем угол поворота камеры
    function whereGoCamera(event)
    {
        degreeCamera += 90 * directionX(event);
    }

    // Обновляем данные каеры для поворота
    function updateCamera()
    {
        if (Math.abs(degreeCamera) === 360 || degreeCamera === 0)
        {
            degreeCamera = 0;
            camera.position.set(0, -(gridSizeY / 2), 10);
            camera.rotation.set(0.5, 0, 0);
        }
        else if (degreeCamera === 90 || degreeCamera === -270)
        {
            camera.position.set(-(gridSizeY / 2), 0, 10);
            camera.rotation.set(0, -0.5, -Math.PI/2);
        }
        else if (Math.abs(degreeCamera) === 180)
        {
            camera.position.set(0, gridSizeX / 2, 10);
            camera.rotation.set(-0.5, 0, Math.PI);
        }
        else if (degreeCamera === -90 || degreeCamera === 270)
        {
            camera.position.set(gridSizeX / 2, 0, 10);
            camera.rotation.set(0, 0.5, Math.PI/2);
        }

        camera.updateMatrixWorld();
    }

    // Масштабирование камеры
    function zoom(event) {
        camera.position.z += directionY;
        event.preventDefault();
    }

    //---------------------------------------------

    function getIntersects(event)
    {
        var mouse = new THREE.Vector2();
        mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
        mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;

        var raycaster = new THREE.Raycaster();
        raycaster.params.PointsCloud = { threshold: 10 };
        raycaster.setFromCamera(mouse, camera);

        return raycaster.intersectObjects(interactiveObjects);
    }

    // Функция для добавления границ на объект при наведении
    function addOutlineOnHover(event)
    {
        if (!isDragging)
        {
            var intersects = getIntersects(event);

            if (outlineMeshSelectedObjectHover) {
                selectedObject.remove(outlineMeshSelectedObjectHover);
                outlineMeshSelectedObjectHover = null;
                selectedObject = null;
            }

            if (intersects.length > 0) {
                selectedObject = intersects[0].object;

                if (blockObjectSelection) {
                    if (blockObjectSelection !== selectedObject) {
                        intersects = null;
                        selectedObject = null;
                    }
                }

                if (selectedObject)
                {
                    intersectionPoint = intersects[0].point;
                    offset.copy(intersectionPoint).sub(selectedObject.position);

                    const outlineMaterial = new THREE.MeshBasicMaterial({color: 0x0fff00, side: THREE.BackSide});
                    outlineMeshSelectedObjectHover = new THREE.Mesh(selectedObject.geometry, outlineMaterial);
                    outlineMeshSelectedObjectHover.scale.set(1.05, 1.05, 1.05);
                    selectedObject.add(outlineMeshSelectedObjectHover);
                }
            }
        }
    }

    // Обновляем новое положение объекта
    function updatePositionSelectedObject (newDot, newZ = null)
    {
        if (newZ === null)
        {
            newZ = axisZ;
        }

        selectedObject.position.set(newDot.x, newDot.y, newZ);

        setColorGridMesh(); // Обновляем тени
    }

    // Поворот объектов вокруг своей оси
    document.getElementById('scene-container').addEventListener('wheel', (event) => {
        if (selectedObject && isDragging)
        {
            const direction = event.deltaY > 0 ? 1 : -1;
            selectedObject.rotation.z += (Math.PI / 2) * direction;

            if (Math.abs(selectedObject.rotation.z / Math.PI) === 2)
                selectedObject.rotation.z = 0;

            // Проверка на необходимость "доворота" фигуры, чтобы попасть в сетку
            if (selectedObjectRotateX || selectedObjectRotateY)
            {
                if (selectedObjectRotatePoint.isEmptyPoint())
                {
                    selectedObjectRotatePoint.addPoint0deg(selectedObject.position.x, selectedObject.position.y);

                    var rotateX = selectedObjectRotateX ? drift : 0;
                    var rotateY = selectedObjectRotateY ? drift : 0;

                    selectedObjectRotatePoint.addPoint90deg(selectedObject.position.x + rotateX - rotateY, selectedObject.position.y + rotateY - rotateX)
                }

                updatePositionSelectedObject(selectedObjectRotatePoint.getPoint());
            }
        }
    });

    function isRotation()
    {
        return Number.isInteger(selectedObject.rotation.z / Math.PI);
    }

    // Отрисовка тени на сцене
    function setColorGridMesh()
    {
        var widthObject = isRotation() ? selectedObject.geometry.parameters.width : selectedObject.geometry.parameters.height;
        var heightObject = isRotation() ? selectedObject.geometry.parameters.height : selectedObject.geometry.parameters.width;

        var dotsObject = [];
        for (var i = 0; i < widthObject * heightObject; i++)
        {
            var oneDot = Object.create(dot);
            oneDot.addDot(i % widthObject - widthObject / 2 + drift + selectedObject.position.x, Math.floor(i / widthObject) - heightObject / 2 + drift + selectedObject.position.y)
            dotsObject.push(oneDot);
        }

        var color = '#00FF00';

        if (isFreedomPosition())
        {
            blockObjectSelection = null;
        }
        else
        {
            blockObjectSelection = selectedObject;
            color = '#FF0000';
        }

        var cellDot = Object.create(dot);
        gridMesh.children.forEach((cell) => {
            cellDot.addDot(cell.position.x, cell.position.y);
            cell.material.color.set('#808080');
            for (var i = 0; i < dotsObject.length; i++)
            {
                if(isEqualsDots(cellDot, dotsObject[i]))
                {
                    cell.material.color.set(color);
                    delete dotsObject[i];
                    break;
                }
            }
        })
    }

    // Проверка пересечения
    function doSegmentsIntersect(firstSegmentMin, firstSegmentMax, secondSegmentMin, secondSegmentMax) {
        if (firstSegmentMax > secondSegmentMin && firstSegmentMin < secondSegmentMin
            || firstSegmentMax > secondSegmentMax && firstSegmentMin < secondSegmentMax)
        {
            return true;
        }

        return false;
    }

    // Определяет свободно ли поле для размещения
    function isFreedomPosition()
    {
        // Рассчитываем ограничивающий параллелипипед для объекта
        const boundingBox = new THREE.Box3().setFromObject(selectedObject);

        for (let i = 0; i < interactiveObjects.length; i++) {
            if (selectedObject != interactiveObjects[i])
            {
                const boundingBoxOtherObject = new THREE.Box3().setFromObject(interactiveObjects[i]);

                // Сравниваем пресечение границ объектов
                if (doSegmentsIntersect(boundingBox.min.x, boundingBox.max.x, boundingBoxOtherObject.min.x, boundingBoxOtherObject.max.x)
                    && doSegmentsIntersect(boundingBox.min.x, boundingBox.max.x, boundingBoxOtherObject.min.x, boundingBoxOtherObject.max.x)) {
                    return false;
                }
            }
        }

        return true;
    }

    // Логика перемещения объекта
    function dragAndDrop(event)
    {
        if (isDragging)
        {
            var intersects = getIntersects(event);

            // Учитываем половину ширины и половину длины объекта при ограничении перемещения
            var halfWidth = isRotation() ? selectedObject.geometry.parameters.width / 2 : selectedObject.geometry.parameters.height / 2;
            var halfHeight = isRotation() ? selectedObject.geometry.parameters.height / 2 : selectedObject.geometry.parameters.width / 2;
            var maxX = gridSizeX / 2 - drift - halfWidth;
            var minX = -gridSizeX / 2 + drift + halfWidth;
            var maxY = gridSizeY / 2 - drift - halfHeight;
            var minY = -gridSizeY / 2 + drift + halfHeight;

            if (intersects.length > 0) {
                intersectionPoint = intersects[0].point;
            }
            else {
                intersectionPoint.x > maxX ? intersectionPoint.x = maxX : (intersectionPoint.x < minX ? intersectionPoint.x = minX : intersectionPoint.x += directionX(event));
                intersectionPoint.y > maxY ? intersectionPoint.y = maxY : (intersectionPoint.y < minY ? intersectionPoint.y = minY : intersectionPoint.y += directionY(event));
            }

            var newX = intersectionPoint.x > maxX ? maxX : intersectionPoint.x;
            var newY = intersectionPoint.y > maxY ? maxY : intersectionPoint.y;

            if (newX < minX)
            {
                newX = -gridSizeX / 2;
            }

            if (newY < minY)
            {
                newY = -gridSizeY / 2 + drift;
            }

            //var newX = Math.max(Math.min(intersectionPoint.x, gridSizeX / 2 - drift - halfWidth), -gridSizeX / 2 + drift + halfWidth);
            //var newY = Math.max(Math.min(intersectionPoint.y, gridSizeY / 2 - drift - halfHeight), -gridSizeY / 2 + drift + halfHeight);

            var rotateWidth = selectedObjectRotateX ? drift : 0;
            var rotateHeight = selectedObjectRotateY ? drift : 0;

            var coordinate = Object.create(selectedObjectRotatePoint);
            coordinate.addPoint0deg(Math.round(newX) + rotateHeight, Math.round(newY) + rotateWidth);
            coordinate.addPoint90deg(Math.round(newX) + rotateWidth, Math.round(newY) + rotateHeight);
            updatePositionSelectedObject(coordinate.getPoint());

            setColorGridMesh();
        }
    }

    function onMouseDown()
    {
        if(selectedObject)
        {
            isDragging = true;

            const outlineMaterial = new THREE.MeshBasicMaterial({color: 0x000000, side: THREE.BackSide});
            outlineMeshSelectedObject = new THREE.Mesh(selectedObject.geometry, outlineMaterial);
            outlineMeshSelectedObject.scale.set(1.05, 1.05, 1.05);
            selectedObject.add(outlineMeshSelectedObject);

            selectedObjectRotateX = selectedObject.geometry.parameters.width % 2 === 0;
            selectedObjectRotateY = selectedObject.geometry.parameters.height % 2 === 0;

            controls.enableZoom = false;
        }
        else
        {
            isRotateCamera = true;
            previousMouseX = event.clientX;
        }
    }

    function onMouseUp()
    {
        isDragging = false;

        if (outlineMeshSelectedObject) {
            selectedObject.remove(outlineMeshSelectedObject);
            outlineMeshSelectedObject = null;
        }

        if (selectedObject)
        {
            if (!blockObjectSelection)
            {
                var newDot = new dot.addDot(selectedObject.position.x, selectedObject.position.y);
                for(var z = axisZ; z > 0.5; z -= 0.01)
                {
                    updatePositionSelectedObject(newDot, z);
                }
            }

            selectedObjectRotateX = false;
            selectedObjectRotateY = false;
            selectedObjectRotatePoint.clear();
        }
        else if (isRotateCamera )
        {
            isRotateCamera = false;
            whereGoCamera(event);
            updateCamera();
        }

        controls.enableZoom = true;
    }

    document.addEventListener('mousemove', dragAndDrop, false);
    document.addEventListener('mousedown', onMouseDown, false);
    document.addEventListener('mouseup', onMouseUp, false);
    document.addEventListener('mousemove', addOutlineOnHover, false);

    //------------------------------------

    function animate()
    {
        requestAnimationFrame( animate );
        renderer.render( scene, camera );
    }
    animate();

</script>