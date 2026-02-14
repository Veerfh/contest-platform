<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Конструктор с проверкой прав доступа
     */
    public function __construct()
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Доступ только для администратора');
        }
    }


    /**
     * Список всех конкурсов
     */
    public function contests()
    {
        $contests = Contest::withCount('submissions')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('admin.contests', compact('contests'));
    }

    /**
     * Форма создания нового конкурса
     */
    public function createContest()
    {
        return view('admin.contests-create');
    }

    /**
     * Сохранение нового конкурса
     */
    public function storeContest(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'deadline_at' => 'required|date|after:now',
            'is_active' => 'sometimes|boolean'
        ]);
        
        Contest::create([
            'title' => $request->title,
            'description' => $request->description,
            'deadline_at' => $request->deadline_at,
            'is_active' => $request->has('is_active')
        ]);
        
        return redirect()->route('admin.contests')
            ->with('success', 'Конкурс успешно создан');
    }

    /**
     * Форма редактирования конкурса
     */
    public function editContest(Contest $contest)
    {
        return view('admin.contests-edit', compact('contest'));
    }

    /**
     * Обновление конкурса
     */
    public function updateContest(Request $request, Contest $contest)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'deadline_at' => 'required|date',
            'is_active' => 'sometimes|boolean'
        ]);
        
        $contest->update([
            'title' => $request->title,
            'description' => $request->description,
            'deadline_at' => $request->deadline_at,
            'is_active' => $request->has('is_active')
        ]);
        
        return redirect()->route('admin.contests')
            ->with('success', 'Конкурс успешно обновлен');
    }

    /**
     * Удаление конкурса
     */
    public function deleteContest(Contest $contest)
    {
        if ($contest->submissions()->count() > 0) {
            return redirect()->route('admin.contests')
                ->with('error', 'Нельзя удалить конкурс, в котором есть работы');
        }
        
        $contest->delete();
        
        return redirect()->route('admin.contests')
            ->with('success', 'Конкурс успешно удален');
    }

    /**
     * Переключение статуса конкурса (активен/неактивен)
     */
    public function toggleContest(Contest $contest)
    {
        $contest->update([
            'is_active' => !$contest->is_active
        ]);
        
        return redirect()->route('admin.contests')
            ->with('success', 'Статус конкурса изменен');
    }


    /**
     * Список всех пользователей
     */
    public function users()
    {
        $users = User::withCount('submissions')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('admin.users', compact('users'));
    }

    /**
     * Форма редактирования пользователя
     */
    public function editUser(User $user)
    {
        $roles = [
            User::ROLE_PARTICIPANT => 'Участник',
            User::ROLE_JURY => 'Жюри',
            User::ROLE_ADMIN => 'Администратор'
        ];
        
        return view('admin.users-edit', compact('user', 'roles'));
    }

    /**
     * Обновление пользователя
     */
    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:participant,jury,admin'
        ]);
        
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role
        ];
        
        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8']);
            $data['password'] = Hash::make($request->password);
        }
        
        $user->update($data);
        
        return redirect()->route('admin.users')
            ->with('success', 'Пользователь успешно обновлен');
    }

    /**
     * Удаление пользователя
     */
    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')
                ->with('error', 'Нельзя удалить самого себя');
        }
        
        if ($user->submissions()->count() > 0) {
            return redirect()->route('admin.users')
                ->with('error', 'Нельзя удалить пользователя, у которого есть работы');
        }
        
        $user->delete();
        
        return redirect()->route('admin.users')
            ->with('success', 'Пользователь успешно удален');
    }

    /**
     * Изменение роли пользователя
     */
    public function changeUserRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:participant,jury,admin'
        ]);
        
        if ($user->id === auth()->id() && $request->role !== User::ROLE_ADMIN) {
            return redirect()->route('admin.users')
                ->with('error', 'Нельзя изменить свою роль с администратора на другую');
        }
        
        $user->update(['role' => $request->role]);
        
        return redirect()->route('admin.users')
            ->with('success', 'Роль пользователя изменена');
    }
}