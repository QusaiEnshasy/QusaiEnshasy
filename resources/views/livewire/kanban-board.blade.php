<div class="p-6" wire:loading.class="opacity-90 transition-opacity duration-200">
    <style>
        [x-cloak] { display: none !important; }

        .task-card {
            transition: transform 180ms ease, box-shadow 180ms ease, opacity 180ms ease;
            transform-origin: center;
            animation: cardIn 240ms ease-out;
        }

        .task-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
        }

        .drag-handle {
            cursor: grab;
            user-select: none;
        }

        .sortable-chosen { opacity: 0.85; }
        .sortable-drag {
            transform: rotate(1deg) scale(1.01);
            box-shadow: 0 12px 24px rgba(30, 41, 59, 0.22);
        }
        .kanban-ghost {
            background: #e0f2fe !important;
            border: 1px dashed #38bdf8;
            opacity: 0.65;
        }

        .column-drop-active {
            outline: 2px dashed #38bdf8;
            outline-offset: -4px;
            background: #f0f9ff;
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <div class="mb-6 bg-white p-4 rounded shadow">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
            <input wire:model.defer="title" placeholder="Task title" class="border p-2 rounded">
            <input wire:model.defer="assigned_user" placeholder="Assign user" class="border p-2 rounded">
            <input wire:model.defer="due_date" type="date" class="border p-2 rounded">
            <select wire:model.defer="label" class="border p-2 rounded">
                <option value="">Label</option>
                <option value="red">Urgent</option>
                <option value="green">Low</option>
                <option value="blue">Feature</option>
            </select>
        </div>

        @error('title') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
        @error('due_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

        <button type="button" wire:click="addTask" wire:loading.attr="disabled" class="mt-3 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition-colors duration-200">
            <span wire:loading.remove wire:target="addTask">Add Task</span>
            <span wire:loading wire:target="addTask">Saving...</span>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach(['todo' => 'Todo', 'progress' => 'Progress', 'done' => 'Done'] as $key => $title)
            <div>
                <h2 class="font-bold mb-3">{{ $title }}</h2>
                <div id="{{ $key }}Column" data-status="{{ $key }}" class="bg-gray-100 p-3 rounded min-h-[300px] space-y-2 transition-all duration-200">
                    @foreach($$key as $task)
                        <div class="task-card bg-white p-3 rounded shadow" data-id="{{ $task->id }}" wire:key="task-{{ $task->id }}">
                            <div class="flex justify-between items-start gap-2">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="drag-handle text-gray-400 hover:text-gray-600" title="Drag">::</span>
                                        <span class="font-bold">{{ $task->title }}</span>
                                    </div>

                                    @if($task->label)
                                        <span class="inline-block mt-1 text-xs px-2 py-1 rounded {{ $task->label == 'red' ? 'bg-red-400' : '' }} {{ $task->label == 'green' ? 'bg-green-400' : '' }} {{ $task->label == 'blue' ? 'bg-blue-400' : '' }} text-white">
                                            {{ $task->label }}
                                        </span>
                                    @endif

                                    @if($task->assigned_user)
                                        <p class="text-xs text-gray-500 mt-1">Assigned: {{ $task->assigned_user }}</p>
                                    @endif
                                    @if($task->due_date)
                                        <p class="text-xs text-gray-500">Due: {{ $task->due_date }}</p>
                                    @endif
                                </div>
                                <div class="space-x-2 shrink-0">
                                    <button type="button" wire:click="editTask({{ $task->id }})" class="text-blue-600 hover:text-blue-800 text-xs">Edit</button>
                                    <button type="button" wire:click="deleteTask({{ $task->id }})" class="text-red-600 hover:text-red-800 text-xs">Delete</button>
                                </div>
                            </div>

                            @if($task->comments->count())
                                <div class="mt-3 border-t pt-2">
                                    <p class="text-xs font-semibold text-gray-600 mb-1">Comments</p>
                                    <ul class="space-y-1">
                                        @foreach($task->comments as $comment)
                                            <li class="text-xs bg-gray-50 rounded px-2 py-1">{{ $comment->content }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if($task->attachments->count())
                                <div class="mt-3 border-t pt-2">
                                    <p class="text-xs font-semibold text-gray-600 mb-1">Attachments</p>
                                    <ul class="space-y-1">
                                        @foreach($task->attachments as $attachment)
                                            <li>
                                                <a class="text-xs text-blue-600 hover:text-blue-800 underline" href="{{ asset('storage/' . $attachment->filepath) }}" target="_blank">
                                                    {{ $attachment->filename }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <div x-data="{ open: $wire.entangle('showEditPopup') }" x-show="open" x-transition.opacity.duration.180ms class="fixed inset-0 bg-black bg-opacity-45 flex items-center justify-center p-3">
        <div x-show="open" x-transition:enter="transition ease-out duration-220" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-180" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="bg-white p-6 rounded w-full md:w-1/2 max-h-[90vh] overflow-y-auto shadow-2xl">
            <h2 class="font-bold mb-4">Edit Task</h2>
            <input wire:model.defer="editTitle" placeholder="Title" class="border p-2 w-full mb-2 rounded">
            <input wire:model.defer="editAssignedUser" placeholder="Assigned User" class="border p-2 w-full mb-2 rounded">
            <input wire:model.defer="editDueDate" type="date" class="border p-2 w-full mb-2 rounded">
            <select wire:model.defer="editLabel" class="border p-2 w-full mb-2 rounded">
                <option value="">Label</option>
                <option value="red">Urgent</option>
                <option value="green">Low</option>
                <option value="blue">Feature</option>
            </select>

            @error('editTitle') <p class="text-red-500 text-xs mb-2">{{ $message }}</p> @enderror

            <div class="mb-3">
                <label class="font-semibold text-sm">Comments</label>
                @foreach($editComments as $i => $comment)
                    <input wire:model.defer="editComments.{{ $i }}" class="border p-2 w-full mb-1 rounded" placeholder="Comment {{ $i + 1 }}">
                @endforeach
                <button type="button" wire:click="addEditCommentField" class="text-blue-600 hover:text-blue-800 text-xs">Add Comment</button>
            </div>

            <div class="mb-3">
                <label class="font-semibold text-sm">Current Attachments</label>
                @if(count($existingAttachments))
                    <ul class="mt-1 space-y-1">
                        @foreach($existingAttachments as $attachment)
                            <li>
                                <a class="text-xs text-blue-600 hover:text-blue-800 underline" href="{{ asset('storage/' . $attachment['filepath']) }}" target="_blank">
                                    {{ $attachment['filename'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-xs text-gray-500 mt-1">No attachments yet.</p>
                @endif
            </div>

            <div class="mb-2">
                <label class="font-semibold text-sm">Add New Attachments</label>
                <input type="file" wire:model="editAttachments" multiple class="border p-2 w-full mb-1 rounded">
                @error('editAttachments.*') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end space-x-2 mt-4">
                <button type="button" wire:click="updateTask" wire:loading.attr="disabled" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition-colors duration-200">
                    <span wire:loading.remove wire:target="updateTask">Save</span>
                    <span wire:loading wire:target="updateTask">Saving...</span>
                </button>
                <button type="button" wire:click="closeEditPopup" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded transition-colors duration-200">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    (() => {
        if (window.__kanbanSortBooted) return;
        window.__kanbanSortBooted = true;
        window.__kanbanSortables = window.__kanbanSortables || {};

        const columnIds = ['todoColumn', 'progressColumn', 'doneColumn'];

        function initKanbanSortable() {
            columnIds.forEach((id) => {
                const el = document.getElementById(id);
                if (!el) return;

                if (window.__kanbanSortables[id]) {
                    window.__kanbanSortables[id].destroy();
                    delete window.__kanbanSortables[id];
                }

                window.__kanbanSortables[id] = new Sortable(el, {
                    group: 'kanban',
                    handle: '.drag-handle',
                    draggable: '.task-card',
                    animation: 220,
                    easing: 'cubic-bezier(0.2, 0.8, 0.2, 1)',
                    ghostClass: 'kanban-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    forceFallback: false,
                    fallbackOnBody: true,
                    swapThreshold: 0.65,
                    delayOnTouchOnly: true,
                    delay: 90,
                    onStart(evt) {
                        evt.from.classList.add('column-drop-active');
                    },
                    onMove(evt) {
                        document.querySelectorAll('[data-status]').forEach((col) => col.classList.remove('column-drop-active'));
                        if (evt.to) evt.to.classList.add('column-drop-active');
                        return true;
                    },
                    onEnd(evt) {
                        document.querySelectorAll('[data-status]').forEach((col) => col.classList.remove('column-drop-active'));

                        const taskId = Number(evt.item.dataset.id);
                        const status = evt.to?.dataset?.status;
                        const componentEl = evt.to?.closest('[wire\\:id]');

                        if (!componentEl || !status || !taskId) return;

                        const component = Livewire.find(componentEl.getAttribute('wire:id'));
                        if (!component) return;

                        component.call('moveTask', taskId, status);
                    },
                });
            });
        }

        function safeInit() {
            requestAnimationFrame(() => initKanbanSortable());
        }

        if (window.Livewire?.hook) {
            Livewire.hook('morph.updated', safeInit);
            Livewire.hook('morph.added', safeInit);
        }

        document.addEventListener('livewire:initialized', safeInit);
        safeInit();
    })();
</script>

