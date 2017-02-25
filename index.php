<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Rates</title>
</head>
<body>
	<div np-app>
		<div ng-controller="hstreelookup">
			<label>Country ISO</label>
			<input type="text" ng-model="hstreedata.country"><br>
			<label>Description</label>
			<input type="text" ng-model="hstreedata.description"><br>
			<button ng-click="performSearch()">Search</button>
			<div ng-show="hasItems()">
				<select ng-options="item for item in list" ng-model="landedcost.hs"></select><br>
				<input type="text" ng-model="landedcost.price" placeholder="price"><br>
				<input type="text" ng-model="landedcost.units" placeholder="units"><br>
                <button ng-click="addProduct()">Add product</button>
			</div>
            <div ng-show="hasProducts()">
                <ul>
                    <li ng-repeat="product in products">{{product.hs}} - {{product.price}}</li>
                </ul>
                <button ng-click="getTaxes()">Get taxes</button>
            </div>
			<textarea style="width: 100%; height: 500px" ng-show="hasTaxes()" ng-model="taxes"></textarea>
		</div>
	</div>

	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.7/angular.min.js"></script>
	<script type="text/javascript">
		var app = angular.module('app', []);
		app.controller('hstreelookup', function($scope, $http) {
			$scope.hstreedata = {};
			$scope.list = [];
			$scope.landedcost = {};
			$scope.taxes = "";
            $scope.products = [];

            $scope.hasProducts = function() {
                return $scope.products.length > 0;
            }

            $scope.addProduct = function() {
				var hsData = $scope.landedcost.hs;
				var item = hsData.match(/\|?_?([0-9A-Z\.]+) \-/)[1];

                $scope.products.push({
                    hs: item,
                    price: $scope.landedcost.price,
                    quantity: $scope.landedcost.units
                });
            }

			$scope.performSearch = function() {
				$scope.list = [];
				$scope.taxes = "";
				$http({
					method: 'POST',
					url: '/HsLookup.php',
					data: $scope.hstreedata
				}).then(function successCallback(response) {
					data = response.data.split("\r\n");
					$scope.list = data.filter(function(n){return n != "";});
				}, function errorCallback(response) {
					console.log(response);
				});
			}

			$scope.hasItems = function() {
				return $scope.list.length > 0;
			}

			$scope.hasTaxes = function() {
				return $scope.taxes != "";
			}

			$scope.getTaxes = function() {
				$scope.taxes = "";

				var data = {
					country: $scope.hstreedata.country,
                    transport: 100,
                    products: $scope.products
				}

				$http({
					method: 'POST',
					url: '/MultipleLandedCost.php',
					data: data
				}).then(function successCallback(response) {
					$scope.taxes = response.data;
				}, function errorCallback(response) {
					$scope.taxes = response.data;
				});
			}
		});

		angular.element(document).ready(function() {
			angular.bootstrap(document, ['app']);
		})
	</script>
</body>
</html>
