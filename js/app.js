const header = { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' };

var app = angular.module('app', ['ngRoute']);

app.config(function ($routeProvider, $locationProvider) {
    $routeProvider.when('/', {
        templateUrl: '/templates/select.html',
        controller: 'selectCtrl as selectCtrl'
    });
    $routeProvider.when('/insert', {
        templateUrl: '/templates/insert.html',
        controller: 'insertCtrl as insertCtrl'
    });
    $routeProvider.when('/update', {
        templateUrl: '/templates/update.html',
        controller: 'updateCtrl as updateCtrl'
    });
    $routeProvider.when('/delete', {
        templateUrl: '/templates/delete.html',
        controller: 'deleteCtrl as deleteCtrl'
    });
    $routeProvider.otherwise(
        {
            templateUrl: '404.html'
        }
    );
    $locationProvider.html5Mode({
        enabled: true,
        requireBase: false
    });
});

app.controller('selectCtrl', function ($http)
{
    this.tableName = '';
    this.name = 'Таблицы';
    this.type = 'tables';
    let update = (dataObject) => {
        $http({
            method: 'POST',
            url: 'query.php',
            data: dataObject,
            headers: header
        }).then(successResponse=>
        {
            let resp = successResponse.data;
            console.log(resp);
            this.records = resp.data;
        }, errorResponse=>
        {
            console.log(errorResponse);
        });
    };

    this.showAllTables = () => {
        if (this.isShowAllTables)
        {
            update({object: 'db', action: 'getTables'});
            this.type = 'tables';
        }
        else
            this.items = [];
    };
    this.selectRecords = () => {
        if(this.tableName.length > 0)
        {
            this.isShowAllTables = false;
            this.items = [];
            update({object: 'table', action: 'selectRecords', table: this.tableName});
            this.type = 'records';
        }


    };
    this.showAllTables();
});


app.controller('insertCtrl', function ($http)
{
    this.tableName = '';
    this.columnList = [];

    this.selectTable = () =>
    {
        this.columnList = [];
        for (key in this.columnData)
            delete this.columnData[key];
        $http({
            method: 'POST',
            url: 'query.php',
            data: {object: 'table', action: 'getColumns', table: this.tableName},
            headers: header
        }).then(successResponse=>
        {
            let resp = successResponse.data;
            this.columnList = resp.data;
        }, errorResponse=>
        {
            console.log(errorResponse);
        });

    };

    this.insertRecord = () =>
    {
        $http({
            method: 'POST',
            url: 'query.php',
            data: {object: 'table', action: 'insertRecord', table: this.tableName, columnData: this.columnData},
            headers: header
        }).then(successResponse=>
        {
            console.log(this.columnData);
            let resp = successResponse.data;
            alert(resp.code);
        }, errorResponse=>
        {
            console.log(errorResponse);
        });

    };

});


app.controller('updateCtrl', function ($http)
{
    this.columnDataList = [];
    this.showColumn = false;
    this.getRecord = () =>
    {
        $http({
            method: 'POST',
            url: 'query.php',
            data: {object: 'table', action: 'getRecord', table: this.tableName, recordId: this.recordId},
            headers: header
        }).then(successResponse=>
        {
            if ("code" in successResponse.data)
            {
                let resp = successResponse.data;
                switch (resp.code)
                {
                    case 'Wrong table name.':
                        alert('Такой таблицы не существует.');
                        break;
                    case 'ok.':
                        this.columnDataList = resp.data;
                        this.showColumn = (Object.keys(resp.data).length > 0);
                        break;
                }
            }
        }, errorResponse=>
        {
            console.log(errorResponse);
        });
    };

    this.updateRecord = () =>
    {
        $http({
            method: 'POST',
            url: 'query.php',
            data: {object: 'table', action: 'updateRecord', table: this.tableName, recordId: this.recordId, columnDataList: this.columnDataList},
            headers: header
        }).then(successResponse=>
        {
            if ("code" in successResponse.data)
                alert(successResponse.data.code);
        }, errorResponse=>
        {
            console.log(errorResponse);
        });
    };

});


app.controller('deleteCtrl', function ($http)
{
    this.deleteRecord = () =>
    {
        $http({
            method: 'POST',
            url: 'query.php',
            data: {object: 'table', action: 'deleteRecord', table: this.tableName, recordId: this.recordId},
            headers: header
        }).then(successResponse=>
        {
            if ("code" in successResponse.data)
                alert(successResponse.data.code);
        }, errorResponse=>
        {
            console.log(errorResponse);
        });
    };

});