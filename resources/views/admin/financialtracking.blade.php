@extends('layouts.admin')

@section('title', 'Financial Tracking')

@section('header', 'Financial Tracking')

@section('content')
    <div class="p-0">
        <div class="bg-white shadow-lg p-6 rounded-lg" x-data="financialTrackingData()" x-init="init()">
            <!-- Tabs Navigation -->
            <div class="border-b border-gray-300">
                <div class="flex space-x-2 overflow-x-auto scrollbar-hide">
                    <template
                        x-for="tab in ['offerings', 'expenses', 'confirmation', 'overview', 'setup', 'summary', 'report']">
                        <button @click="onTabClick(tab)"
                            :class="activeTab === tab ? ' border-b-4 border-blue-600 text-blue-600 font-bold bg-blue-300 shadow-md rounded-md ' : 'text-gray-700 hover:text-blue-500'"
                            class="px-4 py-3 text-sm md:text-base transition-all duration-100"
                            x-text="tab.replace('_', ' ').toUpperCase()">
                        </button>
                    </template>
                </div>
            </div>

            <!-- Setup Tab Content -->
            <div x-show="activeTab === 'setup'" x-cloak class="p-4 bg-gray-50 rounded shadow">
                <h3 class="text-lg lg:text-3xl font-semibold">Setup</h3>
                <p class="text-gray-700 mb-4 text-sm md:text-base">Configure offering categories, expenses, and pledges.</p>

                <!-- Offering Partition Section -->
                <div>
                    <h4 class="text-lg md:text-base lg:text-lg font-semibold">Configure Offering Partition</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-300 bg-white text-sm md:text-base">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border px-4 py-2">ITEM NUMBER</th>
                                    <th class="border px-4 py-2">CATEGORY</th>
                                    <th class="border px-4 py-2">PARTITION (%)</th>
                                    <th class="border px-4 py-2">OFFERINGS</th>
                                    <th class="border px-4 py-2">DESCRIPTION</th>
                                    <th class="border px-4 py-2">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                
                                <template x-for="(row, index) in partitions" :key="index">
                                    <tr>
                                        <td class="border px-4 py-2 text-center" x-text="index + 1"></td>
                                        <td class="border px-4 py-2">
                                            <select class="w-full p-1 border rounded" x-model="row.category" :disabled="index === 0"
                                                @change="saveCategorySelection(row, index, row.category)">
                                                <template x-for="expense in expenses2" :key="expense.id">
                                                    <option :value="expense.description" x-text="expense.description"
                                                        :selected="expense.description === row.category"></option>
                                                </template>
                                            </select>

                                        </td>
                                        <td class="border px-4 py-2">
                                            <input type="number" class="w-full p-1 border rounded text-center"
                                                x-model="row.partition" @input="calculatePartition(row)">
                                        </td>
                                        <td class="border px-4 py-2">
                                            <select class="w-full p-1 border rounded" x-model="row.selectedOfferings"
                                                @change="saveOfferingSelection(row)" multiple>
                                                <template x-for="offering in offerings" :key="offering.id">
                                                    <option :value="offering.id" x-text="offering.category"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td class="border px-4 py-2">
                                            <textarea class="w-full p-1 border rounded" x-model="row.description" readonly
                                                rows="2"></textarea>
                                        </td>
                                        <td class="border px-4 py-2 text-center">
                                            <button @click="deletePartitionRow(row.id, row.description, index)"
                                                class="delete-btn px-2 py-1 bg-red-600 text-white rounded transition-all duration-300 cursor-pointer">
                                                Delete
                                            </button>
 
                                            <style>
                                                .delete-btn:hover {
                                                    transform: scale(1.05);
                                                    /* 5% zoom */
                                                    background-color: #ef4444;
                                                    /* Tailwind red-500 hover */
                                                }
                                            </style>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 flex space-x-2">
                        <button @click="addPartitionRow()"
                            class="add-partition-btn px-4 py-2 bg-green-600 text-white rounded transition-all duration-300 cursor-pointer">
                            Add Row
                        </button>

                        <button @click="savePartitionChanges()"
                            class="save-partition-btn px-4 py-2 bg-blue-600 text-white rounded transition-all duration-300 cursor-pointer">
                            Save Changes
                        </button>

                        <style>
                            .add-partition-btn:hover {
                                transform: scale(1.05);
                                /* 5% zoom */
                                background-color: #16a34a;
                                /* Tailwind green-700 */
                            }

                            .save-partition-btn:hover {
                                transform: scale(1.05);
                                /* 5% zoom */
                                background-color: #3b82f6;
                                /* Tailwind blue-500 */
                            }
                        </style>

                    </div>
                </div>

                <!-- Offerings Section -->
                <div class="mt-6">
                    <h4 class="text-lg md:text-base lg:text-lg font-semibold">Create New Offerings</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-300 bg-white text-sm md:text-base">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border px-4 py-2">ITEM NUMBER</th>
                                    <th class="border px-4 py-2">CATEGORY</th>
                                    <th class="border px-4 py-2">SUBCATEGORY OF</th> <!-- New column -->
                                    <th class="border px-4 py-2">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, index) in offerings" :key="index">
                                    <tr>
                                        <td class="border px-4 py-2 text-center" x-text="index + 1"></td>
                                        <td class="border px-4 py-2">
                                            <input type="text" class="w-full p-1 border rounded" x-model="row.category"
                                                placeholder="Category Name">
                                        </td>
                                        <td class="border px-4 py-2">
                                            <label class="flex items-center space-x-2">
                                                <input type="checkbox" x-model="row.is_subcategory">
                                                <span class="text-sm">Is Subcategory?</span>
                                            </label>
                                            <!-- Only show dropdown if it’s a subcategory -->
                                            <select class="w-full p-1 border rounded mt-1"
                                                x-bind:class="{'hidden': !row.is_subcategory}"
                                                x-model.number="row.parent_id">
                                                <option :value="null">Select Parent</option>
                                                <template
                                                    x-for="parent in offerings.filter(o => o.id !== row.id && !o.is_subcategory)"
                                                    :key="parent.id">
                                                    <option :value="parent.id" x-text="parent.category"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td class="border px-4 py-2 text-center">
                                            <button @click="deleteOfferingRow(row.id, row.category, index)"
                                                class="delete-offering-btn px-2 py-1 bg-red-600 text-white rounded transition-all duration-300 cursor-pointer">
                                                Delete
                                            </button>

                                            <style>
                                                .delete-offering-btn:hover {
                                                    transform: scale(1.05);
                                                    /* 5% zoom */
                                                    background-color: #ef4444;
                                                    /* Tailwind red-500 hover */
                                                }
                                            </style>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 flex space-x-2">
                        <button @click="addOfferingRow()"
                            class="add-offering-btn px-4 py-2 bg-green-600 text-white rounded transition-all duration-300 cursor-pointer">
                            Add Row
                        </button>

                        <button @click="saveOfferingChanges()"
                            class="save-offering-btn px-4 py-2 bg-blue-600 text-white rounded transition-all duration-300 cursor-pointer">
                            Save Changes
                        </button>

                        <style>
                            .add-offering-btn:hover {
                                transform: scale(1.05);
                                /* 5% zoom */
                                background-color: #16a34a;
                                /* Tailwind green-700 */
                            }

                            .save-offering-btn:hover {
                                transform: scale(1.05);
                                /* 5% zoom */
                                background-color: #3b82f6;
                                /* Tailwind blue-500 */
                            }
                        </style>

                    </div>
                </div>

                <!-- Expenses Section -->
                <div class="mt-6">
                    <h4 class="text-lg md:text-base lg:text-lg font-semibold">Create New Expenses</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-300 bg-white">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border px-4 py-2 text-sm md:text-base">ITEM NUMBER</th>
                                    <th class="border px-4 py-2 text-sm md:text-base">DESCRIPTION</th>
                                    <th class="border px-4 py-2 text-sm md:text-base">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, index) in expenses" :key="index">
                                    <tr>
                                        <td class="border px-4 py-2 text-center" x-text="index + 1"></td>
                                        <td class="border px-4 py-2">
                                            <input type="text" class="w-full p-1 border rounded" x-model="row.description">
                                        </td>
                                        <td class="border px-4 py-2 text-center">
                                            <button @click="deleteExpenseRow(row.id, row.description, index)"
                                                class="delete-expense-btn px-2 py-1 bg-red-600 text-white rounded transition-all duration-300 cursor-pointer">
                                                Delete
                                            </button>

                                            <style>
                                                .delete-expense-btn:hover {
                                                    transform: scale(1.05);
                                                    /* 5% zoom */
                                                    background-color: #ef4444;
                                                    /* Tailwind red-500 hover */
                                                }
                                            </style>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 flex space-x-2">
                        <button @click="addExpenseRow()"
                            class="add-expense-btn px-4 py-2 bg-green-600 text-white rounded transition-all duration-300 cursor-pointer">
                            Add Row
                        </button>

                        <button @click="saveExpenseChanges()"
                            class="save-expense-btn px-4 py-2 bg-blue-600 text-white rounded transition-all duration-300 cursor-pointer">
                            Save Changes
                        </button>

                        <style>
                            .add-expense-btn:hover {
                                transform: scale(1.05);
                                /* 5% zoom */
                                background-color: #16a34a;
                                /* Tailwind green-700 */
                            }

                            .save-expense-btn:hover {
                                transform: scale(1.05);
                                /* 5% zoom */
                                background-color: #3b82f6;
                                /* Tailwind blue-500 */
                            }
                        </style>

                    </div>
                </div>


            </div>



            <div x-show="activeTab === 'confirmation'" x-cloak class="p-6 bg-white shadow rounded-md">
                @include('admin.donations', ['donationConfirmations' => $donationConfirmations])
            </div>

            <div x-show="activeTab === 'offerings'" x-cloak class="p-4 bg-gray-50 rounded shadow">
                @include('admin.offerings-tab')
            </div>

            <div x-show="activeTab === 'overview'" x-cloak class="p-4 bg-gray-50 rounded shadow">
                @include('admin.chart-tab')
            </div>

            <div x-show="activeTab === 'expenses'" x-cloak class="p-4 bg-gray-50 rounded shadow">
                @include('admin.expenses-tab')
            </div>

            <div x-show="activeTab === 'summary'" x-cloak class="p-4 bg-gray-50 rounded shadow">
                @include('admin.summary-tab')
            </div>

            <div x-show="activeTab === 'report'" x-cloak class="p-4 bg-gray-50 rounded shadow">
                @include('admin.report-tab')
            </div>

        </div>
    </div>

    <script>
        function financialTrackingData() {
            return {
                activeTab: localStorage.getItem('activeTab') || 'confirmation', // Load from localStorage or default to 'confirmation'
                partitions: @json($partitions ?? []),
                
                expenses: @json($expenses ?? []),
                pledges: @json($pledges ?? []),
                adminBranchId: {{ Auth::user()->branch_id }}, // Get the admin's branch ID

                offerings: (@json($offerings ?? [])).map(o => ({
                    ...o,
                    is_subcategory: o.parent_id !== null, // Automatically mark existing subcategories
                    parent_id: o.parent_id || null
                })),
                expenses2: [
                    ...(@json($expenses ?? [])),
                    ...(@json($offerings ?? []).filter(o => o.parent_id !== null).map(o => ({
                        id: o.id,
                        description: o.category, // or whatever field you want to use as description
                        parent_id: o.parent_id,
                        is_subcategory: true
                    })))
                ],



                addPartitionRow(savedRow = null) {
                    this.partitions.push({
                        id: savedRow?.id || null,
                        category: savedRow?.category || (this.expenses?.[1]?.description || ''),  // ⬅️ Only default if empty
                        partition: savedRow?.partition || 0,
                        selectedOfferings: savedRow?.selectedOfferings || [],
                        description: savedRow?.description || ''
                    });
                    
                },
                
                saveCategorySelection(row, index) {

                    if (row.category === 'GENERAL' && row !== this.partitions[0]) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Not Allowed',
                            text: 'Only the first partition can be assigned to General.',
                        });

                        this.$nextTick(() => {
                            row.category = "UTILITY";
                            this.partitions[index].category = "UTILITY";
                        });
                        return;
                    }

                    this.$nextTick(() => {
                        this.partitions[index].category = row.category;
                    });

                    console.log('🎯 Category selected:', row.category);
                },

                addOfferingRow() {
                    this.offerings.push({
                        category: '',
                        is_subcategory: false,
                        parent_id: null,
                        branch_id: this.adminBranchId
                    });
                },


                addExpenseRow() {
                    this.expenses.push({
                        description: ''
                    });
                },
                addPledgeRow() {
                    this.pledges.push({
                        description: ''
                    });
                },
                deletePartitionRow(id, description, index) {
                    if (index === 0) {
                        Swal.fire('Warning', 'The first partition row cannot be deleted.', 'warning');
                        return;
                    }
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'This will permanently delete the partition.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'No, cancel!',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if (!id) {
                                this.partitions = this.partitions.filter(d => {
                                    if (d.description !== description) return true;
                                    if (d.id) return true;
                                    return false;
                                });
                                return;
                            }
                            fetch(`/financial-tracking/partitions/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire('Deleted!', 'Partition has been deleted.', 'success')
                                            .then(() => window.location.reload());
                                    } else {
                                        Swal.fire('Error', data.error || 'Failed to delete.', 'error');
                                    }
                                });

                        }
                    });
                },
                deleteExpenseRow(id, description, index) {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'This will permanently delete the expense.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'No, cancel!',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if (!id) {
                                this.expenses = this.expenses.filter(d => {
                                    if (d.description !== description) return true;
                                    if (d.id) return true;
                                    return false;
                                });
                                return;
                            }

                            fetch(`/financial-tracking/expenses/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire('Deleted!', 'Expense has been deleted.', 'success')
                                            .then(() => window.location.reload());
                                    } else {
                                        Swal.fire('Error', data.error || 'Failed to delete.', 'error');
                                    }
                                });

                        }
                    });
                },
                deleteOfferingRow(id, category, index) {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'This will permanently delete the offering.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'No, cancel!',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {

                            if (!id) {
                                this.offerings = this.offerings.filter(d => {
                                    if (d.category !== category) return true;
                                    if (d.id) return true;
                                    return false;
                                });
                                return;
                            }
                            fetch(`/financial-tracking/offerings/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire('Deleted!', 'Offering has been deleted.', 'success')
                                            .then(() => window.location.reload());
                                    } else {
                                        Swal.fire('Error', data.error || 'Failed to delete.', 'error');
                                    }
                                });

                        }
                    });
                },
                deletePledgeRow(index) {
                    this.pledges.splice(index, 1);
                },

                init() {
                    this.loadSelections();
                    console.log('Initial partitions data:', this.partitions);
                },

                // Save the active tab to localStorage whenever it changes
                setActiveTab(tab) {
                    this.activeTab = tab;
                    localStorage.setItem('activeTab', tab); // Save the active tab to localStorage
                },

                // Call this method when a tab is clicked
                onTabClick(tab) {
                    this.setActiveTab(tab);
                },

                saveOfferingSelection(row) {
                    localStorage.setItem('selectedOfferings_' + row.id, JSON.stringify(row.selectedOfferings));
                    console.log('Saved offerings for row ID', row.id, ':', row.selectedOfferings);
                },

                loadSelections() {
                    this.partitions.forEach(row => {
                        const savedOfferings = localStorage.getItem('selectedOfferings_' + row.id);
                        if (savedOfferings) {
                            // Parse and convert each to Number to match option values
                            row.selectedOfferings = JSON.parse(savedOfferings).map(id => Number(id));
                            console.log('Loaded offerings for row ID', row.id, ':', row.selectedOfferings);
                        } else {
                            console.log('No saved offerings found for row ID', row.id);
                        }

                        console.log('Row after loading selections:', row);
                    });
                },

                savePartitionChanges() {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'Do you want to save these partition changes?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, save it!',
                        cancelButtonText: 'No, cancel!',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('/financial-tracking/partitions', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ partitions: this.partitions })
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire('Saved!', 'Your partition changes have been saved.', 'success')
                                            .then(() => window.location.reload());
                                    } else {
                                        // Show the error message if backend returns success: false
                                        Swal.fire('Error', data.error || 'Failed to save partitions.', 'error');
                                    }
                                })
                                .catch(err => {
                                    Swal.fire('Error', 'Failed to save partitions.', 'error');
                                    console.error(err);
                                });

                        } else {
                            Swal.fire('Cancelled', 'Your partition changes were not saved.', 'info')
                                .then(() => window.location.reload());

                        }
                    });
                }
                ,
                saveOfferingChanges() {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'Do you want to save these offering changes?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, save it!',
                        cancelButtonText: 'No, cancel!',
                        reverseButtons: true
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            const payload = this.offerings.map(row => {
                                let parentId = row.is_subcategory && row.parent_id ? Number(row.parent_id) : null;
                                return {
                                    id: row.id || null,
                                    category: row.category,
                                    parent_id: parentId,
                                    branch_id: row.branch_id || null
                                };
                            });

                            console.log('💾 Payload being sent to backend:', payload); 

                            await fetch('/financial-tracking/offerings', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ offerings: payload })
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire('Saved!', 'Your offering changes have been saved.', 'success')
                                            .then(() => window.location.reload());
                                    } else {
                                        Swal.fire('Error', data.error || 'Failed to save offerings.', 'error');
                                    }
                                })
                                .catch(err => {
                                    console.error('❌ Fetch error:', err);
                                    Swal.fire('Error', 'Failed to save offerings.', 'error');
                                });

                        } else {
                            Swal.fire('Cancelled', 'Your offering changes were not saved.', 'info')
                                .then(() => window.location.reload());

                        }
                    });
                },



                saveExpenseChanges() {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'Do you want to save these expense changes?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, save it!',
                        cancelButtonText: 'No, cancel!',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('/financial-tracking/expenses', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    expenses: this.expenses
                                })
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire('Saved!', 'Your expense changes have been saved.', 'success')
                                            .then(() => window.location.reload());
                                    }
                                });

                        } else {
                            Swal.fire('Cancelled', 'Your expenses changes were not saved.', 'info')
                                .then(() => window.location.reload());

                        }
                    });
                },

                savePledgeChanges() {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'Do you want to save these pledge changes?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, save it!',
                        cancelButtonText: 'No, cancel!',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('/financial-tracking/pledges', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    pledges: this.pledges
                                })
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire('Saved!', 'Your pledge changes have been saved.', 'success')
                                            .then(() => window.location.reload());
                                    }
                                });

                        } else {
                            Swal.fire('Cancelled', 'Your pledge changes were not saved.', 'info');
                        }
                    });
                },

                calculatePartition(row) {
                    // Calculate current total for the selected offerings
                    let totalForSelected = this.partitions
                        .filter(r => r !== row)
                        .filter(r => r.selectedOfferings.some(id => row.selectedOfferings.includes(id)))
                        .reduce((sum, r) => sum + Number(r.partition || 0), 0);

                    let maxAllowed = 100 - totalForSelected;

                    if (Number(row.partition) > maxAllowed) {
                        row.partition = maxAllowed;
                        Swal.fire('Warning', `Partition cannot exceed 100% for these offerings. Max allowed: ${maxAllowed}%`, 'warning');
                    }

                    // Optional: update description
                    let totalOfferings = this.offerings.reduce((total, offering) => {
                        if (row.selectedOfferings.includes(offering.id)) {
                            return total + (offering.amount || 0);
                        }
                        return total;
                    }, 0);

                    let partitionAmount = (totalOfferings * row.partition) / 100;
                    row.description = `₱${partitionAmount.toFixed(2)}`;
                }

            }


        }
    </script>
@endsection