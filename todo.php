<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo List Application</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e9e9e9;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            min-height: 100vh;
        }
        
        .container {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 100%;
            max-width: 800px;
            margin-top: 30px;
        }
        
        .show-all {
            margin-bottom: 15px;
        }
        
        .show-all label {
            display: flex;
            align-items: center;
            color: #5f9bd5;
            font-weight: 500;
        }
        
        .show-all input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 10px;
            accent-color: #5f9bd5;
        }
        
        .input-row {
            display: flex;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .counter {
            padding: 10px 15px;
            background-color: #f5f5f5;
            color: #666;
            border-right: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 35px;
        }
        
        #taskInput {
            flex: 1;
            padding: 10px 15px;
            border: none;
            font-size: 14px;
            outline: none;
        }
        
        #taskInput::placeholder {
            color: #aaa;
        }
        
        #addTaskBtn {
            padding: 10px 25px;
            background-color: #4caf50;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }
        
        #addTaskBtn:hover {
            background-color: #3e8e41;
        }
        
        .task-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        
        .task-item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
        }
        
        .task-checkbox {
            margin-right: 10px;
            height: 18px;
            width: 18px;
            accent-color: #5f9bd5;
        }
        
        .task-text {
            flex: 1;
            font-size: 14px;
        }
        
        .task-meta {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 15px;
        }
        
        .timestamp {
            color: #888;
            font-size: 12px;
        }
        
        .user-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #ddd;
            overflow: hidden;
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .delete-btn {
            background: none;
            border: none;
            color: #888;
            cursor: pointer;
            font-size: 16px;
            padding: 5px;
        }
        
        .delete-btn:hover {
            color: #f44336;
        }
        
        .completed .task-text {
            text-decoration: line-through;
            color: #888;
        }
        
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 999;
            display: none;
        }
        
        .modal {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        
        .modal h3 {
            margin-top: 0;
            color: #333;
        }
        
        .modal-btns {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        .modal-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
        }
        
        .modal-btn.confirm {
            background-color: #f44336;
            color: white;
        }
        
        .modal-btn.cancel {
            background-color: #9e9e9e;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="show-all">
            <label>
                <input type="checkbox" id="showAllCheckbox" checked>
                Show All Tasks
            </label>
        </div>
        
        <div class="input-row">
            <div class="counter" id="taskCounter">0</div>
            <input type="text" id="taskInput" placeholder="Project # To Do">
            <button id="addTaskBtn">Add</button>
        </div>
        
        <ul id="taskList" class="task-list"></ul>
    </div>
    
    <div class="modal-overlay" id="deleteModal">
        <div class="modal">
            <h3>Are you sure you want to delete this task?</h3>
            <div class="modal-btns">
                <button id="confirmDeleteBtn" class="modal-btn confirm">Yes, Delete</button>
                <button id="cancelDeleteBtn" class="modal-btn cancel">Cancel</button>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // DOM Elements
            const taskInput = document.getElementById('taskInput');
            const addTaskBtn = document.getElementById('addTaskBtn');
            const taskList = document.getElementById('taskList');
            const showAllCheckbox = document.getElementById('showAllCheckbox');
            const taskCounter = document.getElementById('taskCounter');
            const deleteModal = document.getElementById('deleteModal');
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
            
            // Task Array to store all tasks
            let tasks = [];
            let taskToDelete = null;
            
            // Add event listeners
            addTaskBtn.addEventListener('click', addTask);
            taskInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    addTask();
                }
            });
            
            showAllCheckbox.addEventListener('change', filterTasks);
            
            confirmDeleteBtn.addEventListener('click', confirmDelete);
            cancelDeleteBtn.addEventListener('click', closeDeleteModal);
            
            // Functions
            function addTask() {
                const taskText = taskInput.value.trim();
                
                if (taskText === '') {
                    return;
                }
                
                // Check for duplicate tasks
                if (isDuplicate(taskText)) {
                    alert('This task already exists!');
                    return;
                }
                
                // Create new task object
                const task = {
                    id: Date.now(),
                    text: taskText,
                    completed: false,
                    timestamp: 'a few seconds ago',
                    user: {
                        avatar: '/api/placeholder/30/30'
                    }
                };
                
                // Add task to array
                tasks.push(task);
                
                // Clear input
                taskInput.value = '';
                
                // Update task counter
                updateTaskCounter();
                
                // Render tasks
                renderTasks();
                
                // Focus back to input
                taskInput.focus();
            }
            
            function isDuplicate(text) {
                return tasks.some(task => task.text.toLowerCase() === text.toLowerCase());
            }
            
            function toggleTaskStatus(id) {
                tasks = tasks.map(task => {
                    if (task.id === id) {
                        return { ...task, completed: !task.completed };
                    }
                    return task;
                });
                
                renderTasks();
            }
            
            function showDeleteConfirmation(id) {
                taskToDelete = id;
                deleteModal.style.display = 'flex';
            }
            
            function closeDeleteModal() {
                deleteModal.style.display = 'none';
                taskToDelete = null;
            }
            
            function confirmDelete() {
                if (taskToDelete !== null) {
                    tasks = tasks.filter(task => task.id !== taskToDelete);
                    updateTaskCounter();
                    renderTasks();
                    closeDeleteModal();
                }
            }
            
            function filterTasks() {
                renderTasks();
            }
            
            function updateTaskCounter() {
                taskCounter.textContent = tasks.filter(task => !task.completed).length;
            }
            
            function renderTasks() {
                // Clear task list
                taskList.innerHTML = '';
                
                // Filter tasks based on checkbox
                let filteredTasks = tasks;
                
                if (!showAllCheckbox.checked) {
                    filteredTasks = tasks.filter(task => !task.completed);
                }
                
                // Render filtered tasks
                filteredTasks.forEach(task => {
                    const taskItem = document.createElement('li');
                    taskItem.className = 'task-item';
                    if (task.completed) {
                        taskItem.classList.add('completed');
                    }
                    
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.className = 'task-checkbox';
                    checkbox.checked = task.completed;
                    checkbox.addEventListener('change', () => toggleTaskStatus(task.id));
                    
                    const taskText = document.createElement('span');
                    taskText.className = 'task-text';
                    taskText.textContent = task.text;
                    
                    const taskMeta = document.createElement('div');
                    taskMeta.className = 'task-meta';
                    
                    const timestamp = document.createElement('span');
                    timestamp.className = 'timestamp';
                    timestamp.textContent = task.timestamp;
                    
                    const userAvatar = document.createElement('div');
                    userAvatar.className = 'user-avatar';
                    const avatarImg = document.createElement('img');
                    avatarImg.src = task.user.avatar;
                    avatarImg.alt = 'User Avatar';
                    userAvatar.appendChild(avatarImg);
                    
                    const deleteBtn = document.createElement('button');
                    deleteBtn.className = 'delete-btn';
                    deleteBtn.innerHTML = '🗑️';
                    deleteBtn.addEventListener('click', () => showDeleteConfirmation(task.id));
                    
                    taskMeta.appendChild(timestamp);
                    taskMeta.appendChild(userAvatar);
                    taskMeta.appendChild(deleteBtn);
                    
                    taskItem.appendChild(checkbox);
                    taskItem.appendChild(taskText);
                    taskItem.appendChild(taskMeta);
                    taskList.appendChild(taskItem);
                });
            }
        });
    </script>
</body>
</html>