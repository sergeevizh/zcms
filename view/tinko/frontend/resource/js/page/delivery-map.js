ymaps.ready(initDeliveryMap);

// Карта, округа Москвы, стоимость доставки, инициализация
function initDeliveryMap() {
    var deliveryMap = new ymaps.Map('deliveryMap', { center: [55.734853,37.597211], zoom: 10 });
    // deliveryMap.behaviors.enable('scrollZoom');
    //Добавляем элементы управления    
    deliveryMap.controls.add('zoomControl').add('typeSelector').add('mapTools');

    // Создаем многоугольник Центра (в пределах ТТК)
    var centerPolygon = new ymaps.Polygon([[
        [55.785669,37.565486], /* Ленинградский проспект, ТТК */
        [55.791375,37.573897],
        [55.791726,37.575013],
        [55.792125,37.584755],
        [55.792149,37.595355],
        [55.792185,37.596063],
        [55.792572,37.600526],
        [55.793056,37.613165],
        [55.79314,37.616276],
        [55.793044,37.618315],
        [55.792729,37.623658],
        [55.792463,37.628271],
        [55.791907,37.631597],
        [55.791835,37.633142],
        [55.792016,37.63782],
        [55.793358,37.644751],
        [55.793672,37.646424],
        [55.793805,37.648141],
        [55.793745,37.649664],
        [55.793491,37.651338],
        [55.793189,37.652432],
        [55.792512,37.654128],
        [55.792052,37.654793],
        [55.79088,37.656381],
        [55.789042,37.658698],
        [55.786346,37.66226],
        [55.785318,37.663569],
        [55.783033,37.666552],
        [55.781292,37.669727], /* Щелковское шоссе, ТТК */
        [55.780107,37.672216],
        [55.777398,37.679662],
        [55.776817,37.680885],
        [55.776164,37.681872],
        [55.773866,37.685027],
        [55.77256,37.686808],
        [55.770117,37.691013],
        [55.769016,37.691915],
        [55.767431,37.692515],
        [55.766875,37.692558],
        [55.764806,37.692065],
        [55.764491,37.692086],
        [55.759567,37.69258],
        [55.755974,37.692923],
        [55.75509,37.692944],
        [55.753275,37.694618],
        [55.752307,37.695305],
        [55.749257,37.698309],
        [55.748725,37.69876],
        [55.747575,37.699489],
        [55.746909,37.699725],
        [55.746123,37.699768],
        [55.745481,37.699468],
        [55.743678,37.698223],
        [55.742492,37.697665],
        [55.741184,37.697343],
        [55.739986,37.697279],
        [55.738933,37.697472],
        [55.737855,37.697923],
        [55.736681,37.698566],
        [55.735797,37.699296],
        [55.734804,37.70039],
        [55.732443,37.703094],
        [55.731547,37.704274],
        [55.730361,37.705841],
        [55.726123,37.71099],
        [55.724766,37.711892],
        [55.723773,37.711806],
        [55.72324,37.710862],
        [55.721569,37.706184],
        [55.72014,37.701549], /* Волгоградский проспект, ТТК */
        [55.71842,37.695412],
        [55.717427,37.69082],
        [55.715707,37.684769],
        [55.714084,37.680606],
        [55.711734,37.673053],
        [55.703859,37.658548],
        [55.703253,37.656145],
        [55.703301,37.650265],
        [55.704416,37.637391],
        [55.70587,37.622499],
        [55.705846,37.621169],
        [55.705434,37.619667],
        [55.704731,37.618122],
        [55.702599,37.615547],
        [55.701653,37.613701],
        [55.701169,37.611899],
        [55.701023,37.610011],
        [55.701144,37.608122],
        [55.702865,37.602543],
        [55.705676,37.592201],
        [55.707009,37.587695],
        [55.709383,37.583446], /* Ленинский проспект, ТТК */
        [55.715295,37.575421],
        [55.717257,37.570829],
        [55.72106,37.558684],
        [55.721981,37.555251],
        [55.722829,37.553276],
        [55.723991,37.551345],
        [55.725105,37.549886],
        [55.73162,37.543621],
        [55.734284,37.542719],
        [55.735107,37.541732],
        [55.738013,37.537355],
        [55.740143,37.534995],
        [55.743823,37.533664],
        [55.749705,37.532162],
        [55.751327,37.53139],
        [55.752682,37.531561],
        [55.758055,37.533922],
        [55.766306,37.537312],
        [55.767709,37.538213],
        [55.769088,37.540016],
        [55.770395,37.542462],
        [55.77262,37.544865],
        [55.773165,37.545874],
        [55.773527,37.546861],
        [55.773685,37.547612],
        [55.774084,37.55141],
        [55.774398,37.552697],
        [55.775051,37.553856],
        [55.775729,37.554542],
        [55.77689,37.555572],
        [55.779816,37.557847],
        [55.782319,37.561216]
    ]], {
        //Свойства
        hintContent: "Доставка 1000 руб."
    }, {
        // Опции
        // Цвет заливки (красный)
        fillColor: '#FF0000',
        // Прозрачность (полупрозрачная заливка)
        opacity: 0.2,
        // Цвет обводки (красный)
        strokeColor: '#FF0000'
    });
    deliveryMap.geoObjects.add(centerPolygon);
    
    // Добавляем метку Центра (в пределах ТТК)
    var centerPlacemark = new ymaps.Placemark([55.754449,37.622327], {
        // Свойства
        iconContent: '1'
    }, {
        // Опции
        preset: 'twirl#blackStretchyIcon' // иконка растягивается под контент
    });
    deliveryMap.geoObjects.add(centerPlacemark);
    
    // Создаем многоугольник Севера
    var northPolygon = new ymaps.Polygon([[
        [55.785669,37.565486], /* Ленинградский проспект, ТТК */
        [55.800719,37.531132],
        [55.806907,37.507786],
        [55.824402,37.493367],
        [55.839087,37.481522],
        [55.853839,37.471308],
        [55.868803,37.460966],
        [55.870251,37.459721],
        [55.871312,37.458519],
        [55.881602,37.445001], /* Ленинградский проспект, МКАД */
        [55.882229,37.448692],
        [55.882591,37.454013],
        [55.882663,37.458991],
        [55.883098,37.465686],
        [55.883677,37.468605],
        [55.887053,37.48238],
        [55.889755,37.491135],
        [55.896977,37.507593],
        [55.900907,37.516498],
        [55.904005,37.523601],
        [55.905343,37.527506],
        [55.906163,37.530767],
        [55.906898,37.535016],
        [55.908381,37.548985],
        [55.910767,37.57158],
        [55.911032,37.576301],
        [55.910924,37.580356],
        [55.910454,37.585012],
        [55.909815,37.588424],
        [55.906042,37.602329],
        [55.902619,37.614903],
        [55.899726,37.628636],
        [55.897459,37.643055],
        [55.89553,37.662796],
        [55.89471,37.692751],
        [55.894132,37.698588],
        [55.891672,37.707171],
        [55.882265,37.725968],
        [55.866438,37.757039],
        [55.829837,37.82845],
        [55.826842,37.832741],
        [55.824281,37.835488],
        [55.82172,37.837204],
        [55.819497,37.838063],
        [55.813916,37.838835], /* Щелковское шоссе, МКАД */
        [55.813408,37.834565],
        [55.813179,37.831389],
        [55.811849,37.820875],
        [55.810617,37.811562],
        [55.81023,37.798602],
        [55.809771,37.781951],
        [55.809409,37.77899],
        [55.808792,37.776308],
        [55.802351,37.751781],
        [55.795232,37.710733],
        [55.79522,37.70509],
        [55.793352,37.691464],
        [55.789151,37.680596],
        [55.785741,37.676197],
        [55.784363,37.675081],
        [55.781292,37.669727], /* Щелковское шоссе, ТТК */
        [55.783033,37.666552],
        [55.785318,37.663569],
        [55.786346,37.66226],
        [55.789042,37.658698],
        [55.79088,37.656381],
        [55.792052,37.654793],
        [55.792512,37.654128],
        [55.793189,37.652432],
        [55.793491,37.651338],
        [55.793745,37.649664],
        [55.793805,37.648141],
        [55.793672,37.646424],
        [55.793358,37.644751],
        [55.792016,37.63782],
        [55.791835,37.633142],
        [55.791907,37.631597],
        [55.792463,37.628271],
        [55.792729,37.623658],
        [55.793044,37.618315],
        [55.79314,37.616276],
        [55.793056,37.613165],
        [55.792572,37.600526],
        [55.792185,37.596063],
        [55.792149,37.595355],
        [55.792125,37.584755],
        [55.791726,37.575013],
        [55.791375,37.573897]
    ]], {
        //Свойства
        hintContent: "Доставка 1500 руб."
    }, {
        // Опции
        // Цвет заливки
        fillColor: '#FFFF00',
        // Прозрачность (полупрозрачная заливка)
        opacity: 0.2,
        // Цвет обводки
        strokeColor: '#FFFF00'
    });
    deliveryMap.geoObjects.add(northPolygon);
    
    // Добавляем метку Севера
    var northPlacemark = new ymaps.Placemark([55.84743,37.612317], {
        // Свойства
        iconContent: '2'
    }, {
        // Опции
        preset: 'twirl#blackStretchyIcon' // иконка растягивается под контент
    });
    deliveryMap.geoObjects.add(northPlacemark);
    
    // Создаем многоугольник Востока
    var eastPolygon = new ymaps.Polygon([[
        [55.781292,37.669727], /* Щелковское шоссе, ТТК */
        [55.784363,37.675081],
        [55.785741,37.676197],
        [55.789151,37.680596],
        [55.793352,37.691464],
        [55.79522,37.70509],
        [55.795232,37.710733],
        [55.802351,37.751781],
        [55.808792,37.776308],
        [55.809409,37.77899],
        [55.809771,37.781951],
        [55.81023,37.798602],
        [55.810617,37.811562],
        [55.811849,37.820875],
        [55.813179,37.831389],
        [55.813408,37.834565],
        [55.813916,37.838835], /* Щелковское шоссе, МКАД */
        [55.807354,37.839618],
        [55.803971,37.83979],
        [55.776938,37.842622],
        [55.7691,37.843137],
        [55.743932,37.84185],
        [55.730591,37.840434],
        [55.715477,37.838588],
        [55.713224,37.837859],
        [55.70799,37.835069],
        [55.69963,37.830692],
        [55.697836,37.830005],
        [55.696067,37.829533],
        [55.693643,37.829276],
        [55.692116,37.829276],
        [55.690637,37.829447],
        [55.687146,37.830456], /* Волгоградский проспект, МКАД */
        [55.700454,37.796639],
        [55.701738,37.79269],
        [55.702126,37.790738],
        [55.705458,37.765761],
        [55.709165,37.737158],
        [55.70925,37.735699],
        [55.709226,37.731128],
        [55.709323,37.730163],
        [55.716373,37.709134],
        [55.72014,37.701549], /* Волгоградский проспект, ТТК */
        [55.721569,37.706184],
        [55.72324,37.710862],
        [55.723773,37.711806],
        [55.724766,37.711892],
        [55.726123,37.71099],
        [55.730361,37.705841],
        [55.731547,37.704274],
        [55.732443,37.703094],
        [55.734804,37.70039],
        [55.735797,37.699296],
        [55.736681,37.698566],
        [55.737855,37.697923],
        [55.738933,37.697472],
        [55.739986,37.697279],
        [55.741184,37.697343],
        [55.742492,37.697665],
        [55.743678,37.698223],
        [55.745481,37.699468],
        [55.746123,37.699768],
        [55.746909,37.699725],
        [55.747575,37.699489],
        [55.748725,37.69876],
        [55.749257,37.698309],
        [55.752307,37.695305],
        [55.753275,37.694618],
        [55.75509,37.692944],
        [55.755974,37.692923],
        [55.759567,37.69258],
        [55.764491,37.692086],
        [55.764806,37.692065],
        [55.766875,37.692558],
        [55.767431,37.692515],
        [55.769016,37.691915],
        [55.770117,37.691013],
        [55.77256,37.686808],
        [55.773866,37.685027],
        [55.776164,37.681872],
        [55.776817,37.680885],
        [55.777398,37.679662],
        [55.780107,37.672216]
    ]], {
        //Свойства
        hintContent: "Доставка 500 руб."
    }, {
        // Опции
        // Цвет заливки
        fillColor: '#00FF00',
        // Прозрачность (полупрозрачная заливка)
        opacity: 0.2,
        // Цвет обводки
        strokeColor: '#00FF00'
    });
    deliveryMap.geoObjects.add(eastPolygon);
    
    // Добавляем метку Востока
    var eastPlacemark = new ymaps.Placemark([55.756373,37.776232], {
        // Свойства
        iconContent: '3'
    }, {
        // Опции
        preset: 'twirl#blackStretchyIcon' // иконка растягивается под контент
    });
    deliveryMap.geoObjects.add(eastPlacemark);    
    
    // Создаем многоугольник Юга
    var southPolygon = new ymaps.Polygon([[
        [55.72014,37.701549], /* Волгоградский проспект, ТТК */
        [55.716373,37.709134],
        [55.709323,37.730163],
        [55.709226,37.731128],
        [55.70925,37.735699],
        [55.709165,37.737158],
        [55.705458,37.765761],
        [55.702126,37.790738],
        [55.701738,37.79269],
        [55.700454,37.796639],
        [55.687146,37.830456], /* Волгоградский проспект, МКАД */
        [55.682297,37.832323],
        [55.6798,37.833396],
        [55.673058,37.835885],
        [55.663914,37.839189],
        [55.661779,37.839575],
        [55.660299,37.839618],
        [55.658819,37.839533],
        [55.657,37.839146],
        [55.655156,37.838417],
        [55.652705,37.836786],
        [55.651152,37.83537],
        [55.648871,37.832452],
        [55.640546,37.819706],
        [55.626854,37.798849],
        [55.625324,37.796446],
        [55.621002,37.788635],
        [55.619909,37.786146],
        [55.608639,37.765718],
        [55.601836,37.753187],
        [55.591776,37.729326],
        [55.575927,37.687956],
        [55.574323,37.683664],
        [55.573058,37.678858],
        [55.572232,37.674137],
        [55.571843,37.669416],
        [55.575976,37.596632],
        [55.576389,37.592984],
        [55.576876,37.590194],
        [55.581033,37.572642],
        [55.587645,37.544919],
        [55.594547,37.516595],
        [55.596442,37.511016],
        [55.599115,37.506123],
        [55.611068,37.492047],
        [55.638847,37.459517], /* Ленинский проспект, МКАД */
        [55.658552,37.501703],
        [55.687971,37.545262],
        [55.709383,37.583446], /* Ленинский проспект, ТТК */
        [55.707009,37.587695],
        [55.705676,37.592201],
        [55.702865,37.602543],
        [55.701144,37.608122],
        [55.701023,37.610011],
        [55.701169,37.611899],
        [55.701653,37.613701],
        [55.702599,37.615547],
        [55.704731,37.618122],
        [55.705434,37.619667],
        [55.705846,37.621169],
        [55.70587,37.622499],
        [55.704416,37.637391],
        [55.703301,37.650265],
        [55.703253,37.656145],
        [55.703859,37.658548],
        [55.711734,37.673053],
        [55.714084,37.680606],
        [55.715707,37.684769],
        [55.717427,37.69082],
        [55.71842,37.695412]
    ]], {
        //Свойства
        hintContent: "Доставка 1500 руб."
    }, {
        // Опции
        // Цвет заливки
        fillColor: '#FFFF00',
        // Прозрачность (полупрозрачная заливка)
        opacity: 0.2,
        // Цвет обводки
        strokeColor: '#FFFF00'
    });
    deliveryMap.geoObjects.add(southPolygon);
    
    // Добавляем метку Юга
    var southPlacemark = new ymaps.Placemark([55.630885,37.648023], {
        // Свойства
        iconContent: '4'
    }, {
        // Опции
        preset: 'twirl#blackStretchyIcon' // иконка растягивается под контент
    });
    deliveryMap.geoObjects.add(southPlacemark);    
    
    // Создаем многоугольник Запада
    var westPolygon = new ymaps.Polygon([[
        [55.709383,37.583446], /* Ленинский проспект, ТТК */
        [55.687971,37.545262],
        [55.658552,37.501703],
        [55.638847,37.459517], /* Ленинский проспект, МКАД */
        [55.659535,37.435249],
        [55.664169,37.4307],
        [55.674647,37.422889],
        [55.682043,37.417267],
        [55.684297,37.41598],
        [55.685825,37.415379],
        [55.688467,37.41452],
        [55.690867,37.41289],
        [55.69317,37.410658],
        [55.701896,37.398942],
        [55.708802,37.389801],
        [55.710692,37.38787],
        [55.712073,37.386754],
        [55.723434,37.380789],
        [55.731547,37.376669],
        [55.742564,37.371133],
        [55.745977,37.369631],
        [55.749584,37.368987],
        [55.751859,37.368944],
        [55.764975,37.369245],
        [55.782392,37.369846],
        [55.784569,37.370103],
        [55.787035,37.370961],
        [55.793418,37.376455],
        [55.80159,37.384179],
        [55.805892,37.387098],
        [55.809759,37.388728],
        [55.832663,37.395638],
        [55.834933,37.39611],
        [55.837227,37.396024],
        [55.83957,37.395423],
        [55.841719,37.394221],
        [55.844279,37.392462],
        [55.8458,37.391947],
        [55.847731,37.391904],
        [55.848987,37.392247],
        [55.849977,37.392762],
        [55.85915,37.397741],
        [55.860598,37.398599],
        [55.864459,37.402032],
        [55.867403,37.405723],
        [55.869334,37.408941],
        [55.870902,37.411946],
        [55.873122,37.417267],
        [55.874328,37.421001],
        [55.879298,37.437008],
        [55.881023,37.442716],
        [55.881602,37.445001], /* Ленинградский проспект, МКАД */
        [55.871312,37.458519],
        [55.870251,37.459721],
        [55.868803,37.460966],
        [55.853839,37.471308],
        [55.839087,37.481522],
        [55.824402,37.493367],
        [55.806907,37.507786],
        [55.800719,37.531132],
        [55.785669,37.565486], /* Ленинградский проспект, ТТК */
        [55.782319,37.561216],
        [55.779816,37.557847],
        [55.77689,37.555572],
        [55.775729,37.554542],
        [55.775051,37.553856],
        [55.774398,37.552697],
        [55.774084,37.55141],
        [55.773685,37.547612],
        [55.773527,37.546861],
        [55.773165,37.545874],
        [55.77262,37.544865],
        [55.770395,37.542462],
        [55.769088,37.540016],
        [55.767709,37.538213],
        [55.766306,37.537312],
        [55.758055,37.533922],
        [55.752682,37.531561],
        [55.751327,37.53139],
        [55.749705,37.532162],
        [55.743823,37.533664],
        [55.740143,37.534995],
        [55.738013,37.537355],
        [55.735107,37.541732],
        [55.734284,37.542719],
        [55.73162,37.543621],
        [55.725105,37.549886],
        [55.723991,37.551345],
        [55.722829,37.553276],
        [55.721981,37.555251],
        [55.72106,37.558684],
        [55.717257,37.570829],
        [55.715295,37.575421]
    ]], {
        //Свойства
        hintContent: "Доставка 2000 руб."
    }, {
        // Опции
        // Цвет заливки
        fillColor: '#FF00FF',
        // Прозрачность (полупрозрачная заливка)
        opacity: 0.2,
        // Цвет обводки
        strokeColor: '#FF00FF'
    });
    deliveryMap.geoObjects.add(westPolygon);
    
    // Добавляем метку Запада
    var westPlacemark = new ymaps.Placemark([55.753445,37.453702], {
        // Свойства
        iconContent: '5'
    }, {
        // Опции
        preset: 'twirl#blackStretchyIcon' // иконка растягивается под контент
    });
    deliveryMap.geoObjects.add(westPlacemark);
}