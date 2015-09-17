function TodoCtrl($scope) {
  Parse.initialize(window.appID, window.jsKey);
  var Todo = Parse.Object.extend("Todo");

  function getTodos() {
    var query = new Parse.Query(Todo);
    query.find({
      success: function(results) {
        $scope.$apply(function() {
          $scope.todos = results.map(function(obj) {
            return {
              text: obj.get("text"),
              done: obj.get("done"),
              author: obj.get("author"),
              parseObject: obj
            };
          });
        });
      },
      error: function(error) {
        alert("Error: " + error.code + " " + error.message);
      }
    });
  }

  $scope.author = 'John Doe';

  getTodos();

  $scope.addTodo = function() {
    var todo = new Todo();
    todo.save({
      text: $scope.todoText,
      done: false,
      author: $scope.author
    }, {
      success: function(todoAgain) {
        $scope.$apply(function() {
          $scope.todos.push({
              text: todoAgain.get("text"),
              done: todoAgain.get("done"),
              author: todoAgain.get("author"),
              parseObject: todoAgain}
          );
          $scope.todoText = "";
        });
      },
      error: function(error) {
        alert("Error: " + error.code + " " + error.message);
      }
    });
  };

  $scope.checkTodo = function(todo) {
    var parseTodo = todo.parseObject;
    var status;
    if (parseTodo.get("done") == false) {
      status = true;
    } else {
      status = false;
    }
    parseTodo.save({
      done: status
    },{
      success: function(todoAgain) {},
      error: function(error) {
        alert("Error: " + error.code + " " + error.message);
      }
    });
  }

  $scope.remaining = function() {
    var count = 0;
    angular.forEach($scope.todos, function(todo) {
      count += todo.done ? 0 : 1;
    });
    return count;
  };

}