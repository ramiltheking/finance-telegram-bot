class OperationEditor {
    constructor(tg) {
        this.tg = tg;
        this.modal = null;
        this.form = null;
        this.currentOperation = null;
        this.userCategories = {
            income: [],
            expense: []
        };
        this.systemCategories = {
            income: [],
            expense: []
        };

        this.init();
    }

    async init() {
        await this.loadCategories();
        this.initializeModal();
    }

    async loadCategories() {
        try {
            const response = await fetch('/miniapp/dashboard/categories', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    initData: this.tg.initData
                })
            });

            if (response.ok) {
                const data = await response.json();
                this.systemCategories = data.system_categories || { income: [], expense: [] };
                this.userCategories = data.user_categories || { income: [], expense: [] };
            }
        } catch (error) {
            console.error('Ошибка загрузки категорий:', error);
        }
    }

    isConflictingWithSystemCategory(type, categoryName) {
        if (!categoryName) return false;

        const systemCategoryNames = this.systemCategories[type].map(cat =>
            (cat.title || cat.name || '').toLowerCase().trim()
        );

        const userCategoryName = categoryName.toLowerCase().trim();

        return systemCategoryNames.includes(userCategoryName);
    }

    isConflictingWithUserCategory(type, categoryName) {
        if (!categoryName) return false;

        const userCategoryNames = this.userCategories[type].map(cat =>
            (cat.title || cat.name || '').toLowerCase().trim()
        );

        const newCategoryName = categoryName.toLowerCase().trim();

        return userCategoryNames.includes(newCategoryName);
    }

    initializeModal() {
        this.modal = document.getElementById('editOperationModal');
        this.form = document.getElementById('editOperationForm');

        if (!this.modal || !this.form) {
            console.error('Модальное окно или форма не найдены');
            return;
        }

        this.setupEventListeners();
    }

    setupEventListeners() {
        const closeBtn = this.modal.querySelector('.close');
        const cancelBtn = document.getElementById('cancelEdit');
        const categorySelect = document.getElementById('editCategory');

        closeBtn.addEventListener('click', () => this.closeModal());
        cancelBtn.addEventListener('click', () => this.closeModal());
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) this.closeModal();
        });

        categorySelect.addEventListener('change', (e) => this.handleCategoryChange(e.target.value));

        this.form.addEventListener('submit', (e) => this.handleFormSubmit(e));

        this.setupRealTimeValidation();
    }

    handleCategoryChange(selectedValue) {
        const customCategoryGroup = document.getElementById('customCategoryGroup');
        const isCustom = selectedValue === 'custom';

        if (customCategoryGroup) {
            customCategoryGroup.style.display = isCustom ? 'block' : 'none';
        }

        if (isCustom) {
            const customCategoryInput = document.getElementById('editCustomCategory');
            if (customCategoryInput) customCategoryInput.focus();
        }
    }

    setupRealTimeValidation() {
        const amountInput = document.getElementById('editAmount');
        const customCategoryInput = document.getElementById('editCustomCategory');
        const descriptionInput = document.getElementById('editDescription');

        if (amountInput) {
            amountInput.addEventListener('input', () => this.validateAmount(amountInput.value));
        }
        if (customCategoryInput) {
            customCategoryInput.addEventListener('input', (e) => {
                this.filterCategoryInput(e);
                this.validateCustomCategory(customCategoryInput.value);
            });

            customCategoryInput.addEventListener('paste', (e) => {
                setTimeout(() => {
                    this.filterCategoryInput(e);
                    this.validateCustomCategory(customCategoryInput.value);
                }, 0);
            });
        }
        if (descriptionInput) {
            descriptionInput.addEventListener('input', () => this.validateDescription(descriptionInput.value));
        }
    }

    filterCategoryInput(e) {
        const input = e.target;
        let value = input.value;

        const filteredValue = value.replace(/[^a-zA-Zа-яА-ЯёЁ\s\-'&]/g, '');

        if (value !== filteredValue) {
            input.value = filteredValue;
        }
    }

    isValidCategoryName(categoryName) {
        const validCategoryRegex = /^[a-zA-Zа-яА-ЯёЁ\s\-'&]+$/;
        return validCategoryRegex.test(categoryName);
    }

    validateAmount(value) {
        const errorElement = document.getElementById('amountError');
        if (!errorElement) return true;

        if (!value || parseFloat(value) <= 0) {
            this.showError(errorElement, window.i18n.invalid_amount);
            return false;
        }
        this.hideError(errorElement);
        return true;
    }

    validateCustomCategory(value) {
        const errorElement = document.getElementById('customCategoryError');
        if (!errorElement) return true;

        const trimmedValue = value.trim();

        if (!trimmedValue) {
            this.showError(errorElement, window.i18n.field_required);
            return false;
        }
        if (trimmedValue.length > 50) {
            this.showError(errorElement, window.i18n.category_too_long);
            return false;
        }

        if (!this.isValidCategoryName(trimmedValue)) {
            this.showError(errorElement, window.i18n.invalid_category_chars || 'Название категории может содержать только буквы и пробелы');
            return false;
        }

        const operationType = document.getElementById('editOperationType')?.value;
        if (operationType && this.isConflictingWithSystemCategory(operationType, trimmedValue)) {
            this.showError(errorElement, window.i18n.category_conflict_system || 'Эта категория конфликтует с системной категорией. Выберите другое название.');
            return false;
        }

        if (operationType && this.isConflictingWithUserCategory(operationType, trimmedValue)) {
            this.showError(errorElement, window.i18n.category_conflict_user || 'Эта категория уже существует. Выберите другую категорию из списка.');
            return false;
        }

        this.hideError(errorElement);
        return true;
    }

    validateDescription(value) {
        const errorElement = document.getElementById('descriptionError');
        if (!errorElement) return true;

        if (value.length > 255) {
            this.showError(errorElement, window.i18n.description_too_long);
            return false;
        }
        this.hideError(errorElement);
        return true;
    }

    showError(element, message) {
        if (!element) return;
        element.textContent = message;
        element.classList.add('show');
        const inputElement = element.previousElementSibling;
        if (inputElement) inputElement.classList.add('error');
    }

    hideError(element) {
        if (!element) return;
        element.textContent = '';
        element.classList.remove('show');
        const inputElement = element.previousElementSibling;
        if (inputElement) inputElement.classList.remove('error');
    }

    clearErrors() {
        document.querySelectorAll('.error-message').forEach(el => {
            if (el) {
                el.textContent = '';
                el.classList.remove('show');
            }
        });
        document.querySelectorAll('.error').forEach(el => {
            if (el) el.classList.remove('error');
        });
    }

    openEditModal(operation) {
        this.currentOperation = operation;

        document.getElementById('editOperationId').value = operation.id;
        document.getElementById('editOperationType').value = operation.type;
        document.getElementById('editAmount').value = operation.amount;
        document.getElementById('editDescription').value = operation.description || '';

        this.populateCategorySelect(operation.type, operation.category);

        this.clearErrors();

        this.modal.style.display = 'block';
    }

    populateCategorySelect(type, currentCategory) {
        const categorySelect = document.getElementById('editCategory');
        if (!categorySelect) return;

        categorySelect.innerHTML = '<option value="">' + (window.i18n.choose_category || 'Выберите категорию') + '</option>';

        const userCategories = this.userCategories[type] || [];
        if (userCategories.length > 0) {
            const userGroup = document.createElement('optgroup');
            userGroup.label = 'Мои категории';

            userCategories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.name || category;
                option.textContent = category.title || category.name || category;
                userGroup.appendChild(option);
            });

            categorySelect.appendChild(userGroup);
        }

        const customOption = document.createElement('option');
        customOption.value = 'custom';
        customOption.textContent = window.i18n.create_new_category || '+ Создать новую категорию';
        categorySelect.appendChild(customOption);

        const isUserCategory = userCategories.some(cat =>
            (cat.name || cat) === currentCategory || (cat.title && cat.title === currentCategory)
        );

        const isSystemCategory = this.isConflictingWithSystemCategory(type, currentCategory);

        if (isSystemCategory) {
            this.showSystemCategoryInfo(currentCategory);
            categorySelect.value = "";
        } else if (isUserCategory) {
            categorySelect.value = currentCategory;
            document.getElementById('customCategoryGroup').style.display = 'none';
        } else {
            categorySelect.value = 'custom';
            const customCategoryGroup = document.getElementById('customCategoryGroup');
            if (customCategoryGroup) {
                customCategoryGroup.style.display = 'block';
                document.getElementById('editCustomCategory').value = currentCategory;
            }
        }
    }

    showSystemCategoryInfo(category) {
        const infoElement = document.createElement('div');
        infoElement.className = 'system-category-info';
        infoElement.innerHTML = `
            <div class="info-message">
                <span class="info-icon">ℹ️</span>
                <p>Текущая категория "<strong>${category}</strong>" является системной и не может быть отредактирована через это окно.</p>
                <p style="margin-top: 8px; font-size: 13px;">Выберите пользовательскую категорию или создайте новую.</p>
            </div>
        `;

        const form = document.getElementById('editOperationForm');
        if (form) {
            form.insertBefore(infoElement, form.firstChild);
        }
    }

    async handleFormSubmit(e) {
        e.preventDefault();

        const formData = new FormData(e.target);
        const operationId = document.getElementById('editOperationId').value;
        const operationType = document.getElementById('editOperationType').value;
        const amount = formData.get('amount');
        const category = formData.get('category');
        const customCategory = formData.get('custom_category');
        const description = formData.get('description');

        let isValid = true;

        if (!this.validateAmount(amount)) isValid = false;

        if (category === 'custom') {
            if (!this.validateCustomCategory(customCategory)) isValid = false;
        } else if (!category) {
            const categoryError = document.getElementById('categoryError');
            if (categoryError) {
                this.showError(categoryError, window.i18n.field_required);
            }
            isValid = false;
        } else {
            const categoryError = document.getElementById('categoryError');
            if (categoryError) this.hideError(categoryError);
        }

        if (!this.validateDescription(description)) isValid = false;

        if (!isValid) {
            this.showGlobalError(window.i18n.fix_form_errors);
            return;
        }

        await this.updateOperation(operationId, {
            amount: parseFloat(amount),
            category: category === 'custom' ? customCategory.trim() : category,
            description: description.trim(),
            type: operationType
        });
    }

    async updateOperation(operationId, data) {
        try {
            const saveBtn = document.getElementById('saveEdit');
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.textContent = 'Сохранение...';
            }

            const updateRes = await fetch('/miniapp/operations/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    operationId: operationId,
                    ...data,
                    initData: this.tg.initData
                })
            });

            const result = await updateRes.json();

            if (result.success) {
                this.closeModal();

                await this.loadCategories();

                if (window.reloadAllData) {
                    await window.reloadAllData();
                }

                this.showSuccess(window.i18n.edit_success || 'Операция успешно обновлена');
            } else {
                throw new Error(result.message || 'Ошибка обновления');
            }

        } catch (error) {
            console.error('Ошибка обновления операции:', error);
            this.showGlobalError(window.i18n.edit_error || 'Ошибка при обновлении операции');
        } finally {
            const saveBtn = document.getElementById('saveEdit');
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.textContent = window.i18n.save || 'Сохранить';
            }
        }
    }

    closeModal() {
        if (this.modal) {
            this.modal.style.display = 'none';
        }
        if (this.form) {
            this.form.reset();
        }
        this.clearErrors();
        this.currentOperation = null;

        const infoElement = document.querySelector('.system-category-info');
        if (infoElement) {
            infoElement.remove();
        }
    }

    showGlobalError(message) {
        Swal.fire({
            toast: true,
            position: 'bottom-end',
            icon: 'error',
            title: message,
            showConfirmButton: false,
            timer: 3000,
            customClass: {
                popup: 'app-toast error',
                title: 'app-toast-title',
                timerProgressBar: 'app-toast-progress'
            }
        });
    }

    showSuccess(message) {
        Swal.fire({
            toast: true,
            position: 'bottom-end',
            icon: 'success',
            title: message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            customClass: {
                popup: 'app-toast',
                title: 'app-toast-title',
                timerProgressBar: 'app-toast-progress'
            }
        });
    }
}

let operationEditor = null;

function initOperationEditor(tg) {
    operationEditor = new OperationEditor(tg);
}

function openEditModal(operation) {
    if (operationEditor) {
        operationEditor.openEditModal(operation);
    }
}
