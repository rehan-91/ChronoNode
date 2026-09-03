<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Models\Department;
use App\Models\Office;
use App\Models\User;
use App\Services\EmployeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function __construct(
        private readonly EmployeeService $employeeService
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $query = User::with([
            'department:id,name', 
            'office:id,name', 
            'manager:id,name', 
            'shift:id,name'
        ]);

        if ($request->filled('search')) {
            $search = clone $query;
            $searchString = $request->input('search');
            $query->where(function ($q) use ($searchString) {
                $q->where('name', 'like', "%{$searchString}%")
                  ->orWhere('email', 'like', "%{$searchString}%")
                  ->orWhere('employee_code', 'like', "%{$searchString}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }

        if ($request->filled('office_id')) {
            $query->where('office_id', $request->input('office_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        
        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        // Apply scoped visibility if user is a Manager without HrAdmin rights
        if ($request->user()->role->value === 'manager') {
            $query->where('manager_id', $request->user()->id);
        }

        $employees = $query->orderBy('name')->paginate(15)->withQueryString();

        return Inertia::render('Employees/Index', [
            'employees' => $employees,
            'filters' => $request->only(['search', 'department_id', 'office_id', 'status', 'role']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('Employees/Create', [
            'departments' => Department::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'offices' => Office::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'managers' => User::whereIn('role', ['manager', 'super_admin', 'hr_admin'])
                                ->where('status', 'active')
                                ->orderBy('name')
                                ->get(['id', 'name']),
            // 'shifts' => Shift::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $employee = $this->employeeService->createEmployee($request->validated());

        return redirect()->route('employees.show', $employee)->with('success', 'Employee created successfully.');
    }

    public function show(User $employee): Response
    {
        $this->authorize('view', $employee);

        $employee->load(['department', 'office', 'manager', 'shift']);

        return Inertia::render('Employees/Show', [
            'employee' => $employee,
        ]);
    }

    public function edit(User $employee): Response
    {
        $this->authorize('update', $employee);

        return Inertia::render('Employees/Edit', [
            'employee' => $employee,
            'departments' => Department::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'offices' => Office::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'managers' => User::whereIn('role', ['manager', 'super_admin', 'hr_admin'])
                                ->where('status', 'active')
                                ->where('id', '!=', $employee->id)
                                ->orderBy('name')
                                ->get(['id', 'name']),
        ]);
    }

    public function update(UpdateEmployeeRequest $request, User $employee): RedirectResponse
    {
        $this->employeeService->updateEmployee($employee, $request->validated());

        return redirect()->route('employees.show', $employee)->with('success', 'Employee updated successfully.');
    }

    public function deactivate(User $employee): RedirectResponse
    {
        $this->authorize('delete', $employee);

        $this->employeeService->deactivateEmployee($employee);

        return back()->with('success', 'Employee deactivated successfully.');
    }
}
