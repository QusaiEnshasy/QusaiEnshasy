<?php

namespace App\Http\Livewire;

use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class KanbanBoard extends Component
{
    use WithFileUploads;

    public $title = '';
    public $assigned_user = '';
    public $due_date = '';
    public $label = '';

    public $todo;
    public $progress;
    public $done;

    public $editTaskId;
    public $editTitle = '';
    public $editAssignedUser = '';
    public $editDueDate = '';
    public $editLabel = '';
    public $showEditPopup = false;
    public $editComments = [];
    public $editAttachments = [];
    public $existingAttachments = [];

    public function mount()
    {
        $this->loadTasks();
    }

    public function loadTasks()
    {
        $baseQuery = Task::with(['comments', 'attachments'])->orderBy('order');

        $this->todo = (clone $baseQuery)->where('status', 'todo')->get();
        $this->progress = (clone $baseQuery)->where('status', 'progress')->get();
        $this->done = (clone $baseQuery)->where('status', 'done')->get();
    }

    public function addTask()
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'assigned_user' => ['nullable', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
            'label' => ['nullable', Rule::in(['red', 'green', 'blue'])],
        ]);

        $order = Task::max('order') ?? 0;

        Task::create([
            'title' => trim($validated['title']),
            'assigned_user' => $validated['assigned_user'] ? trim($validated['assigned_user']) : null,
            'due_date' => $validated['due_date'] ?: null,
            'label' => $validated['label'] ?: null,
            'order' => $order + 1,
            'status' => 'todo',
        ]);

        $this->reset(['title', 'assigned_user', 'due_date', 'label']);
        $this->loadTasks();
    }

    public function editTask($id)
    {
        $task = Task::with(['comments', 'attachments'])->findOrFail($id);

        $this->editTaskId = $task->id;
        $this->editTitle = (string) $task->title;
        $this->editAssignedUser = (string) ($task->assigned_user ?? '');
        $this->editDueDate = $task->due_date ? (string) $task->due_date : '';
        $this->editLabel = (string) ($task->label ?? '');

        $this->editComments = $task->comments->pluck('content')->map(fn ($c) => (string) $c)->toArray();
        $this->existingAttachments = $task->attachments->toArray();
        $this->editAttachments = [];

        $this->showEditPopup = true;
    }

    public function addEditCommentField()
    {
        $this->editComments[] = '';
    }

    public function updateTask()
    {
        $validated = $this->validate([
            'editTaskId' => ['required', 'integer', 'exists:tasks,id'],
            'editTitle' => ['required', 'string', 'max:255'],
            'editAssignedUser' => ['nullable', 'string', 'max:255'],
            'editDueDate' => ['nullable', 'date'],
            'editLabel' => ['nullable', Rule::in(['red', 'green', 'blue'])],
            'editComments.*' => ['nullable', 'string', 'max:1000'],
            'editAttachments.*' => ['nullable', 'file', 'max:10240'],
        ]);

        DB::transaction(function () use ($validated) {
            $task = Task::findOrFail($validated['editTaskId']);

            $task->update([
                'title' => trim($validated['editTitle']),
                'assigned_user' => $validated['editAssignedUser'] ? trim($validated['editAssignedUser']) : null,
                'due_date' => $validated['editDueDate'] ?: null,
                'label' => $validated['editLabel'] ?: null,
            ]);

            $task->comments()->delete();
            foreach ($this->editComments as $comment) {
                $content = trim((string) $comment);
                if ($content !== '') {
                    $task->comments()->create(['content' => $content]);
                }
            }

            foreach ((array) $this->editAttachments as $file) {
                if ($file instanceof TemporaryUploadedFile) {
                    $path = $file->store('attachments', 'public');
                    $task->attachments()->create([
                        'filename' => $file->getClientOriginalName(),
                        'filepath' => $path,
                    ]);
                }
            }
        });

        $this->showEditPopup = false;
        $this->editAttachments = [];
        $this->loadTasks();
    }

    public function closeEditPopup()
    {
        $this->showEditPopup = false;
    }

    public function deleteTask($id)
    {
        Task::findOrFail($id)->delete();
        $this->loadTasks();
    }

    public function moveTask($id, $status)
    {
        if (!in_array($status, ['todo', 'progress', 'done'], true)) {
            return;
        }

        $task = Task::findOrFail($id);
        if ($task->status === $status) {
            return;
        }

        $task->status = $status;
        $task->order = (Task::where('status', $status)->max('order') ?? 0) + 1;
        $task->save();

        $this->loadTasks();
    }

    public function render()
    {
        return view('livewire.kanban-board');
    }
}
