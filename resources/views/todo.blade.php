<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo List</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .completed { text-decoration: line-through; color: gray; }
        #taskList { text-align: left; padding-left: 0; }
        #taskList .list-group-item { text-align: left; }
        .toggle-status { margin-right: 10px; }
        .task-title { margin-left: 10px; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <div class="form-check">
                    <input type="checkbox" id="showAllTasks" class="form-check-input"> 
                    <label for="showAllTasks" class="form-check-label">Show All Tasks</label>
                </div>
            </div>
            <div class="card-body">
                <div class="input-group mb-3">
                    <input type="text" id="taskInput" class="form-control" placeholder="Project # To Do">
                    <button id="addTask" class="btn btn-success">Add</button>
                </div>
                <ul id="taskList" class="list-group text-start">
                    <!-- Tasks will be appended here -->
                </ul>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            // Track if we're showing all tasks
            let showingAllTasks = false;

            function fetchTasks() {
                $.get('/tasks', function (tasks) {
                    $('#taskList').html('');
                    tasks.forEach(task => {
                        $('#taskList').append(renderTask(task));
                    });
                    // Apply visibility rules after loading tasks
                    updateTaskVisibility();
                });
            }

            function renderTask(task) {
                return `
                    <li class="list-group-item d-flex justify-content-between align-items-center" data-id="${task.id}">
                        <div class="d-flex align-items-center" style="flex: 1;">
                            <input type="checkbox" class="toggle-status" data-id="${task.id}" ${task.completed ? 'checked' : ''}>
                            <span class="task-title ${task.completed ? 'completed' : ''}" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${task.title}</span>
                        </div>
                        <button class="btn btn-danger btn-sm delete-task" data-id="${task.id}">🗑</button>
                    </li>`;
            }

            // Handle showing/hiding completed tasks
            function updateTaskVisibility() {
                if (showingAllTasks) {
                    $('#taskList .list-group-item').show();
                } else {
                    $('#taskList .list-group-item').each(function() {
                        if ($(this).find('.task-title').hasClass('completed')) {
                            $(this).hide();
                        } else {
                            $(this).show();
                        }
                    });
                }
            }

            // Add task
            $('#addTask').click(function () {
                let title = $('#taskInput').val().trim();
                if (!title) return alert('Task cannot be empty');
                
                $.post('/tasks', { title: title }, function (task) {
                    $('#taskList').append(renderTask(task));
                    $('#taskInput').val('');
                    $(`#taskList li[data-id="${task.id}"]`).show();
                }).fail(function(err) {
                    if (err.status === 422) {
                        alert('This task already exists!');
                    } else {
                        alert('An error occurred. Please try again.');
                    }
                });
            });

            
            $('#taskInput').keypress(function(e) {
                if (e.which === 13) { 
                    $('#addTask').click();
                    return false;
                }
            });

            // Delete task
            $(document).on('click', '.delete-task', function () {
                if (!confirm('Are you sure to delete this task?')) return;
                let id = $(this).data('id');
                
                $.ajax({
                    url: `/tasks/${id}`,
                    type: 'DELETE',
                    success: () => $(this).closest('li').remove()
                });
            });

            // Toggle task completion
            $(document).on('change', '.toggle-status', function () {
                let id = $(this).data('id');
                let completed = $(this).is(':checked');
                let listItem = $(this).closest('li');
                
                $.ajax({
                    url: `/tasks/${id}/toggle`,
                    type: 'PATCH',
                    data: { completed: completed },
                    success: (task) => {
                        // Update task appearance
                        $(this).siblings('.task-title').toggleClass('completed', task.completed);
                        
                        // Update visibility based on current filter
                        if (task.completed && !showingAllTasks) {
                            listItem.fadeOut();
                        } else {
                            listItem.fadeIn();
                        }
                    }
                });
            });
            
            // Toggle showing all tasks
            $('#showAllTasks').change(function() {
                showingAllTasks = $(this).prop('checked');
                updateTaskVisibility();
            });
            
            // Initial fetch
            fetchTasks();
        });
    </script>
</body>
</html>