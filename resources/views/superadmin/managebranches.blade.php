@extends('layouts.superadmin')

@section('title', 'Manage Branches')

@section('header', 'Manage Branches')

@section('content')
    <div class="bg-white p-6 rounded shadow">
        <!-- Header with Add Branch Button -->
        <div class="flex justify-between items-center mb-4">
            <h2 id="branchListTitle" class="text-2xl lg:text-3xl font-semibold">Branch List</h2>
            <div class="flex gap-2">
                <button id="toggleArchiveBtn" onclick="toggleArchiveView()"
                    class="bg-gray-500 text-white px-3 py-1 rounded text-base md:text-base lg:text-lg
                                   transition-all duration-300 transform shadow-md hover:bg-gray-600 hover:shadow-lg hover:scale-105 flex items-center gap-1">
                    <i class="fas fa-archive"></i> View Archive
                </button>
                <button onclick="openAddModal()"
                    class="bg-blue-500 text-white px-3 py-1 rounded text-base md:text-base lg:text-lg
                                   transition-all duration-300 transform shadow-md hover:bg-blue-600 hover:shadow-lg hover:scale-105 flex items-center gap-1">
                    <i class="fas fa-plus"></i> Add Branch
                </button>
            </div>
        </div>

        <!-- Branch Table -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border ">
                <thead>
                    <tr class="bg-gray-400 text-base md:text-base lg:text-xl">
                        <th class="p-3 border font-semibold">Branch Name</th>
                        <th class="p-3 border font-semibold">Location</th>
                        <th class="p-3 border font-semibold">Type</th>
                        <th class="p-3 border font-semibold">Date Created</th>
                        <th class="p-3 border font-semibold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="branchTable" class="text-base md:text-base lg:text-lg">
                    <!-- Data Will Load Here -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Branch Modal -->
    <div id="branchModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden p-4 z-50 ">
        <div class="bg-white p-6 rounded shadow w-full max-w-xl mx-auto overflow-auto max-h-[90vh]">
            <h2 id="modalTitle" class="text-lg md:text-lg lg:text-2xl font-semibold mb-4">Add Branch</h2>
            <input type="hidden" id="branchId">
            <input id="branchName" type="text" placeholder="Branch Name" class="border p-2 rounded w-full mb-2">
            <input id="branchLocation" type="text" placeholder="Location" class="border p-2 rounded w-full mb-2">
            <div id="branchTypeContainer" class="mb-2"></div>
            <div class="flex justify-end">
                <button onclick="closeBranchModal()"
                    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-2">Cancel</button>
                <button onclick="saveBranch()"
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Save</button>
            </div>
        </div>
    </div>


    <!-- View Branch Modal -->
    <div id="viewBranchModal"
        class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden p-4 z-50">
        <div
            class="bg-white p-6 rounded shadow w-full max-w-2xl mx-auto overflow-auto max-h-[90vh] text-base md:text-base lg:text-xl">
            <h2 class=" font-bold mb-4 text-base md:text-base lg:text-2xl text-center">Branch Details</h2>
            <div id="branchDetailsContent" class="mb-4 break-words "></div>
            <div class="flex justify-end">
                <button onclick="closeViewBranchModal()"
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Close</button>
            </div>
        </div>
    </div>

    <!-- Create Extension Modal (Working Version) -->
    <div id="extensionModal"
        class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden p-4 z-50 ">
        <div class="bg-white p-6 rounded shadow-lg w-full max-w-xl mx-auto overflow-auto max-h-[90vh]">
            <h2 class="text-lg md:text-lg lg:text-2xl font-semibold mb-4">Create Extension Branch</h2>

            <input type="hidden" id="extensionParentId">

            <div class="mb-3">
                <label class="block text-base md:text-base lg:text-xl font-medium text-gray-700 mb-1">Extension Branch
                    Name</label>
                <input id="extensionName" type="text" placeholder="Branch Name" class="border p-2 rounded w-full">
            </div>

            <div class="mb-4">
                <label class="block text-base md:text-base lg:text-xl font-medium text-gray-700 mb-1">Location</label>
                <input id="extensionLocation" type="text" placeholder="Branch Location" class="border p-2 rounded w-full">
            </div>

            <div class="flex justify-end mt-4 text-base md:text-base lg:text-lg">
                <button onclick="closeExtensionModal()"
                    class="bg-gray-500 text-white px-3 py-1 rounded mr-2 hover:bg-gray-600">
                    Cancel
                </button>
                <button onclick="saveExtension()" class="bg-purple-600 text-white px-3 py-1 rounded hover:bg-purple-700">
                    Create
                </button>
            </div>
        </div>
    </div>


    <style>
        .table-cell-ellipsis {
            max-width: 180px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
            vertical-align: middle;
        }

        .extension-row {
            background-color: #f9fafb;
        }

        .expand-toggle {
            cursor: pointer;
            user-select: none;
            margin-right: 6px;
            font-weight: bold;
            display: inline-block;
            transition: transform 0.2s ease-in-out;
        }

        .rotate-down {
            transform: rotate(90deg);
        }
    </style>

    <script>
       
        let branches = [];
        let selectedBranchId = null;
        let isArchivedView = false;
        function loadBranches(isArchived = false) {
       
            if (typeof isArchived === 'undefined' || isArchived === null) {
                isArchived = false;
            }
            isArchivedView = isArchived;
            const url = isArchived ? '/branches?archived=1' : '/branches';

            fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                credentials: 'same-origin' 
            })
                .then(res => res.json())
                .then(data => {
                    branches = data;
                    renderBranches();
                 
                    updateTitleAndButton();
                })
                .catch(err => console.error('Error loading branches:', err));
        }

        function updateTitleAndButton() {
            const btn = document.getElementById('toggleArchiveBtn');
            const title = document.getElementById('branchListTitle');
            if (isArchivedView) {
                btn.innerHTML = '<i class="fas fa-list"></i> View Branch';
                title.textContent = 'Archived Branches';
            } else {
                btn.innerHTML = '<i class="fas fa-archive"></i> View Archive';
                title.textContent = 'Branch List';
            }
        }

      
        document.addEventListener("DOMContentLoaded", () => {
            loadBranches(false);
            updateTitleAndButton();
        });

        function toggleArchiveView() {
            isArchivedView = !isArchivedView;
            const btn = document.getElementById('toggleArchiveBtn');
            const title = document.getElementById('branchListTitle');
            if (isArchivedView) {
                btn.innerHTML = '<i class="fas fa-list"></i> View Branch';
                title.textContent = 'Archived Branches';
            } else {
                btn.innerHTML = '<i class="fas fa-archive"></i> View Archive';
                title.textContent = 'Branch List';
            }
            loadBranches(isArchivedView);
        }

        function renderBranches() {
            let table = document.getElementById("branchTable");
            table.innerHTML = "";

           
            let filteredBranches = branches;
            if (isArchivedView) {
                filteredBranches = branches.filter(b => b.is_archived === 1 || b.is_archived === true);
            } else {
                filteredBranches = branches.filter(b => b.is_archived === 0 || b.is_archived === false);
            }

          
            const branchMap = new Map();
            filteredBranches.forEach(b => branchMap.set(b.id, b));

          

            const extensionsMap = new Map();
            filteredBranches.forEach(branch => {
                if (branch.extension_of) {
                    if (!extensionsMap.has(branch.extension_of)) {
                        extensionsMap.set(branch.extension_of, []);
                    }
                    extensionsMap.get(branch.extension_of).push(branch);
                }
            });

            filteredBranches.forEach(branch => {
               
                if (!branch.extension_of) {
                    const isOrganized = branch.branch_type === 'Organized';
                    const hasExtensions = extensionsMap.has(branch.id);

                 
                    let expandToggle = '';
                    if (hasExtensions) {
                        expandToggle = `<span class="expand-toggle" onclick="toggleExtensions(${branch.id})" id="toggle-${branch.id}">▶</span>`;
                    }

                    let displayName = branch.name;

                    let row = `<tr class="hover:bg-gray-100 main-branch-row" data-branch-id="${branch.id}">
                        <td class="p-3 border table-cell-ellipsis" title="${displayName}">${expandToggle}${displayName}</td>
                        <td class="p-3 border table-cell-ellipsis" title="${branch.address}">${branch.address}</td>
                        <td class="p-3 border">${branch.branch_type ?? '-'}</td>
                        <td class="p-3 border">${branch.created_at.split('T')[0]}</td>
        <td class="p-3 border text-center align-middle">
            <div class="relative inline-block text-left">
                <div class="flex gap-2 justify-center">

        <button onclick="viewBranch(${branch.id})" 
                title="View" 
                class="bg-gray-700 text-white px-2 py-1 rounded text-xs 
                       transition-all duration-300 transform shadow-md hover:bg-gray-900 hover:shadow-lg hover:scale-105 flex items-center justify-center">
            <i class="fas fa-eye"></i>
        </button>

        <button onclick="editBranch(${branch.id})" 
                title="Edit" 
                class="bg-blue-500 text-white px-2 py-1 rounded text-xs 
                       transition-all duration-300 transform shadow-md hover:bg-blue-600 hover:shadow-lg hover:scale-105 flex items-center justify-center">
            <i class="fas fa-edit"></i>
        </button>

        ${isArchivedView
                            ? `<button onclick="confirmUnarchive(${branch.id})" 
                        title="Unarchive" 
                        class="bg-green-500 text-white px-2 py-1 rounded text-xs 
                               transition-all duration-300 transform shadow-md hover:bg-green-600 hover:shadow-lg hover:scale-105 flex items-center justify-center">
                    <i class="fas fa-undo"></i>
               </button>`
                            : `<button onclick="confirmArchive(${branch.id})" 
                        title="Archive" 
                        class="bg-yellow-500 text-white px-2 py-1 rounded text-xs 
                               transition-all duration-300 transform shadow-md hover:bg-yellow-600 hover:shadow-lg hover:scale-105 flex items-center justify-center">
                    <i class="fas fa-archive"></i>
               </button>`
                        }


                    <div>

        <button onclick="toggleDropdown(${branch.id})" 
                title="More" 
                class="bg-gray-600 text-white px-2 py-1 rounded text-xs 
                       transition-all duration-300 transform shadow-md hover:bg-gray-800 hover:shadow-lg hover:scale-105 flex items-center justify-center">
            <i class="fas fa-ellipsis-h"></i>
        </button>

                        <div id="dropdown-${branch.id}" class="hidden absolute right-0 mt-1 w-48 bg-white border rounded shadow z-10">
        ${(branch.branch_type === 'Organized' || branch.branch_type === 'Mission' || branch.branch_type === 'Main')
                            ? `<button onclick="openExtendModal(${branch.id})" class="block w-full px-4 py-2 text-sm text-left hover:bg-gray-100">
                <i class="fas fa-code-branch mr-2"></i> Add Extension
            </button>`
                            : ''
                        }

        ${branch.branch_type === 'Mission'
                            ? `<button 
                class="block w-full px-4 py-2 text-sm text-left hover:bg-gray-100" 
                data-id="${branch.id}" 
                data-name="${branch.name}" 
                data-address="${branch.address}" 
                onclick="promoteToOrganized(this)">
                <i class="fas fa-level-up-alt mr-2"></i> Upgrade to Organized
            </button>`
                            : ''
                        }
    </div>

                    </div>
                </div>
            </div>
        </td>
                    </tr>`;

                    table.innerHTML += row;

                  
                    if (hasExtensions) {
                        extensionsMap.get(branch.id).forEach(extension => {
                            let extDisplayName = `${extension.name} (Extension of ${branch.name})`;
                            let extRow = `<tr class="extension-row" data-parent-id="${branch.id}" style="display: none;">

                                <td class="p-3 border table-cell-ellipsis" title="${extDisplayName}">${extDisplayName}</td>
                                <td class="p-3 border table-cell-ellipsis" title="${extension.address}">${extension.address}</td>
                                <td class="p-3 border">Extension</td>
                                <td class="p-3 border">${extension.created_at.split('T')[0]}</td>
                                <td class="p-3 border text-center align-middle">
                                    <div class="relative inline-block text-left">
                                        <div class="flex gap-2 justify-center">
                                            <button onclick="viewBranch(${extension.id})" title="View" class="bg-gray-700 text-white px-2 py-1 rounded hover:bg-gray-900 text-xs">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button onclick="editBranch(${extension.id})" title="Edit" class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 text-xs">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                             <button onclick="confirmDelete(${extension.id})" title="Archive" class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600 text-xs">
            <i class="fas fa-archive"></i>
        </button>
                                            <div>
                                              <button onclick="toggleDropdown(${extension.id})" title="More" class="bg-gray-600 text-white px-2 py-1 rounded hover:bg-gray-800 text-xs">
                                                  <i class="fas fa-ellipsis-h"></i>
                                              </button>
                                                <div id="dropdown-${extension.id}" class="hidden absolute right-0 mt-1 w-48 bg-white border rounded shadow z-10">
                                            <button 
                                                class="block w-full px-4 py-2 text-sm text-left hover:bg-gray-100" 
                                                data-id="${extension.id}" 
                                                data-name="${extension.name}" 
                                                data-address="${extension.address}" 
                                                onclick="promoteToOrganized(this)">
                                                <i class="fas fa-level-up-alt mr-2"></i> Make Organized
                                            </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>`;
                            table.innerHTML += extRow;
                        });
                    }
                }
            });
        }


        function openAddModal() {
            document.getElementById("modalTitle").innerText = "Add Branch";
            document.getElementById("branchId").value = "";
            document.getElementById("branchName").value = "";
            document.getElementById("branchLocation").value = "";
            document.getElementById("branchModal").classList.remove("hidden");

            const hasMainBranch = branches.some(b => b.branch_type?.toLowerCase() === 'main');

          
            let selectHTML = `
                <select id="branchType" class="border p-2 rounded w-full">
                    <option value="">Select Branch Type</option>
                    ${!hasMainBranch ? '<option value="Main">Main</option>' : ''}
                    <option value="Mission">Mission</option>
                    <option value="Organized">Organized</option>
                </select>
            `;
            document.getElementById("branchTypeContainer").innerHTML = selectHTML;
        }

        function buildBranchTypeSelect(selectedType = '') {
            const hasMainBranch = branches.some(b => b.branch_type?.toLowerCase() === 'main');
            let selectHTML = `
                <select id="branchType" class="border p-2 rounded w-full">
                    <option value="">Select Branch Type</option>
                    ${!hasMainBranch ? '<option value="Main">Main</option>' : ''}
                    <option value="Mission">Mission</option>
                    <option value="Organized">Organized</option>
                </select>
            `;
            document.getElementById("branchTypeContainer").innerHTML = selectHTML;
            if (selectedType) {
                document.getElementById("branchType").value = selectedType;
            }
        }

        function editBranch(id) {
            let branch = branches.find(b => b.id === id);
            if (branch) {
                document.getElementById("modalTitle").innerText = "Edit Branch";
                document.getElementById("branchId").value = branch.id;
                document.getElementById("branchName").value = branch.name;
                document.getElementById("branchLocation").value = branch.address;
                buildBranchTypeSelect(branch.branch_type || '');
                document.getElementById("branchModal").classList.remove("hidden");
            }
        }

        function toggleDropdown(id) {
            document.querySelectorAll('[id^="dropdown-"]').forEach(el => el.classList.add('hidden'));
            document.getElementById(`dropdown-${id}`).classList.toggle('hidden');
        }
        async function saveBranch() {
            let id = document.getElementById("branchId").value;
            let name = document.getElementById("branchName").value.trim();
            let address = document.getElementById("branchLocation").value.trim();
            let branch_type = document.getElementById("branchType").value;

            if (!name || !address) {
                Swal.fire('Missing Info', 'Please fill all fields.', 'warning');
                return;
            }

            let method = id ? 'PUT' : 'POST';
            let url = id ? `/branches/${id}` : '/branches';

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                credentials: 'same-origin',
                body: JSON.stringify({ name, address, branch_type })
            })
                .then(async res => {
                    let data;
                    try {
                        data = await res.json();
                    } catch (e) {
                        data = {};
                    }

                    if (!res.ok) {
                     
                        let message = data.error
                            || (data.errors ? Object.values(data.errors).flat().join('<br>') : 'Validation failed');

                        Swal.fire('Validation Error', message, 'warning');
                        throw new Error(message);
                    }

                    closeBranchModal();
                    loadBranches(isArchivedView);
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: id ? 'Branch updated successfully.' : 'Branch added successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                })
                .catch(err => {
                    console.error('Fetch error:', err);
                    if (!err.message.includes('Validation')) {
                        Swal.fire('Error', 'Failed to save branch.', 'error');
                    }
                });
        }


        function confirmDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This branch will be archived.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, archive it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/branches/${id}/archive`, {
                        method: 'PATCH',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                loadBranches(isArchivedView);
                                Swal.fire('Archived!', 'Branch has been archived.', 'success');
                            } else {
                                throw new Error(data.message || 'Failed to archive branch.');
                            }
                        })
                        .catch(error => {
                            console.error(error);
                            Swal.fire('Error', error.message, 'error');
                        });
                }
            });
        }



        function closeBranchModal() {
            document.getElementById("branchModal").classList.add("hidden");
        }

        function viewBranch(id) {
            let branch = branches.find(b => b.id === id);
            if (branch) {
                let html = `
                    <div><strong>Branch Name:</strong> ${branch.name}</div>
                    <div><strong>Location:</strong> ${branch.address}</div>
                    <div><strong>Date Created:</strong> ${branch.created_at.split('T')[0]}</div>
                `;
                document.getElementById('branchDetailsContent').innerHTML = html;
                document.getElementById('viewBranchModal').classList.remove('hidden');
            }
        }

        function closeViewBranchModal() {
            document.getElementById('viewBranchModal').classList.add('hidden');
        }





        function openExtendModal(parentId) {
            document.getElementById('extensionParentId').value = parentId;
            document.getElementById('extensionName').value = '';
            document.getElementById('extensionLocation').value = '';
            document.getElementById('extensionModal').classList.remove('hidden');
        }

        function closeExtensionModal() {
            document.getElementById('extensionModal').classList.add('hidden');
        }
        async function saveExtension() {
            const parentId = document.getElementById('extensionParentId').value;
            const name = document.getElementById('extensionName').value.trim();
            const address = document.getElementById('extensionLocation').value.trim();

            if (!name || !address) {
                Swal.fire('Missing Info', 'Please complete all fields.', 'warning');
                return;
            }

            try {
                Swal.fire({
                    title: 'Saving...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                let response = await fetch('/branches', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        name,
                        address,
                        branch_type: 'Extension',
                        extension_of: parentId
                    })
                });

                let data;
                try {
                    data = await response.json();
                } catch (e) {
                    data = {};
                }

                if (!response.ok) {
                    Swal.fire('Error', data.error || (data.errors ? Object.values(data.errors).flat().join('<br>') : 'Failed to create extension.'), 'error');
                    return;
                }

                closeExtensionModal();
                loadBranches();

                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Extension created successfully.',
                    timer: 1500,
                    showConfirmButton: false
                });
            } catch (err) {
                console.error('Fetch error:', err);
                Swal.fire('Error', 'Something went wrong.', 'error');
            }
        }


        function unlinkExtension(id) {
            fetch(`/branches/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                credentials: 'same-origin',
                body: JSON.stringify({ extension_of: null })
            })
                .then(res => {
                    if (!res.ok) throw new Error("Unlink failed");
                    return res.json();
                })
                .then(() => {
                    loadBranches();
                    Swal.fire('Unlinked', 'Branch is no longer an extension.', 'success');
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Error', err.message, 'error');
                });
        }

        function promoteToOrganized(button) {
            const id = button.dataset.id;
            const name = button.dataset.name;
            const address = button.dataset.address;

            fetch(`/branches/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    name: name,
                    address: address,
                    branch_type: 'Organized',
                    extension_of: null
                })
            })
                .then(res => {
                    if (!res.ok) throw new Error("Promotion failed");
                    return res.json();
                })
                .then(() => {
                    loadBranches(); 
                    Swal.fire('Promoted', 'Branch is now Organized.', 'success');
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Error', err.message, 'error');
                });
        }



        function toggleExtensions(parentId) {
            const toggleIcon = document.getElementById(`toggle-${parentId}`);
            const extensionRows = document.querySelectorAll(`tr.extension-row[data-parent-id="${parentId}"]`);
            const isHidden = extensionRows.length > 0 && extensionRows[0].style.display === 'none';

            extensionRows.forEach(row => {
                row.style.display = isHidden ? '' : 'none';
            });

            if (toggleIcon) {
                toggleIcon.textContent = isHidden ? '▼' : '▶';
                toggleIcon.classList.toggle('rotate-down', isHidden);
            }

        
            document.querySelectorAll('[id^="dropdown-"]').forEach(el => el.classList.add('hidden'));
        }


    
        document.addEventListener("click", function (event) {
            const isDropdownButton = event.target.closest("button[onclick^='toggleDropdown']");
            const isDropdownMenu = event.target.closest("[id^='dropdown-']");

            if (!isDropdownButton && !isDropdownMenu) {
                document.querySelectorAll("[id^='dropdown-']").forEach(el => el.classList.add("hidden"));
            }
        });
        function confirmArchive(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This branch will be archived.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, archive it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/branches/${id}/archive`, {
                        method: 'PATCH',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        credentials: 'same-origin'
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                loadBranches(isArchivedView);
                                Swal.fire('Archived!', 'Branch has been archived.', 'success');
                            } else {
                                throw new Error(data.message || 'Failed to archive branch.');
                            }
                        })
                        .catch(error => {
                            console.error(error);
                            Swal.fire('Error', error.message, 'error');
                        });
                }
            });
        }

        function confirmUnarchive(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This branch will be restored.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#22c55e',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, restore it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/branches/${id}/unarchive`, {
                        method: 'PATCH',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        credentials: 'same-origin'
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                loadBranches(isArchivedView);
                                Swal.fire('Restored!', 'Branch has been unarchived.', 'success');
                            } else {
                                throw new Error(data.message || 'Failed to restore branch.');
                            }
                        })
                        .catch(error => {
                            console.error(error);
                            Swal.fire('Error', error.message, 'error');
                        });
                }
            });
        }

    </script>

@endsection