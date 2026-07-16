/**
 * Custom Dropdown Component
 * Handles all custom dropdown functionality
 */

class CustomDropdown {
    constructor(element, options = {}) {
        this.wrapper = element;
        this.select = element.querySelector('select');
        this.options = {
            searchable: options.searchable || false,
            placeholder: options.placeholder || 'Pilih Opsi',
            ...options
        };
        
        this.init();
    }
    
    init() {
        if (!this.select) return;
        
        // Hide original select
        this.select.style.display = 'none';
        
        // Create custom dropdown structure
        this.createCustomDropdown();
        
        // Bind events
        this.bindEvents();
        
        // Set initial value if exists
        if (this.select.value) {
            this.setValue(this.select.value);
        }
    }
    
    createCustomDropdown() {
        const customSelect = document.createElement('div');
        customSelect.className = 'custom-select';
        if (this.select.disabled) {
            customSelect.classList.add('disabled');
        }
        
        // Create trigger
        const trigger = document.createElement('div');
        trigger.className = 'custom-select__trigger';
        trigger.setAttribute('tabindex', '0');
        
        const selectedValue = this.select.options[this.select.selectedIndex];
        const displayText = selectedValue ? selectedValue.text : this.options.placeholder;
        
        trigger.innerHTML = `
            <span class="${!selectedValue || !this.select.value ? 'custom-select__placeholder' : ''}">${displayText}</span>
            <div class="arrow"></div>
        `;
        
        // Create dropdown
        const dropdown = document.createElement('div');
        dropdown.className = 'custom-select__dropdown';
        
        // Add search if enabled
        if (this.options.searchable) {
            const searchWrapper = document.createElement('div');
            searchWrapper.className = 'custom-select__search';
            searchWrapper.innerHTML = `
                <input type="text" placeholder="Cari..." autocomplete="off">
            `;
            dropdown.appendChild(searchWrapper);
        }
        
        // Add options
        Array.from(this.select.options).forEach((option, index) => {
            if (option.value === '' && index === 0) return; // Skip placeholder option
            
            const optionElement = document.createElement('div');
            optionElement.className = 'custom-select__option';
            optionElement.setAttribute('data-value', option.value);
            optionElement.textContent = option.text;
            
            if (option.disabled) {
                optionElement.classList.add('disabled');
            }
            
            if (option.selected) {
                optionElement.classList.add('selected');
            }
            
            dropdown.appendChild(optionElement);
        });
        
        customSelect.appendChild(trigger);
        customSelect.appendChild(dropdown);
        this.wrapper.appendChild(customSelect);
        
        this.customSelect = customSelect;
        this.trigger = trigger;
        this.dropdown = dropdown;
    }
    
    bindEvents() {
        // Toggle dropdown
        this.trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggle();
        });
        
        // Keyboard navigation
        this.trigger.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.toggle();
            }
        });
        
        // Option selection
        this.dropdown.querySelectorAll('.custom-select__option').forEach(option => {
            option.addEventListener('click', (e) => {
                if (!option.classList.contains('disabled')) {
                    this.selectOption(option);
                }
            });
        });
        
        // Search functionality
        if (this.options.searchable) {
            const searchInput = this.dropdown.querySelector('.custom-select__search input');
            searchInput.addEventListener('input', (e) => {
                this.filterOptions(e.target.value);
            });
            
            searchInput.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }
        
        // Close on outside click
        document.addEventListener('click', (e) => {
            if (!this.wrapper.contains(e.target)) {
                this.close();
            }
        });
        
        // Close on escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.close();
            }
        });
    }
    
    toggle() {
        if (this.dropdown.classList.contains('active')) {
            this.close();
        } else {
            this.open();
        }
    }
    
    open() {
        // Close all other dropdowns
        document.querySelectorAll('.custom-select__dropdown.active').forEach(dropdown => {
            dropdown.classList.remove('active');
            dropdown.previousElementSibling.classList.remove('active');
        });
        
        this.dropdown.classList.add('active');
        this.trigger.classList.add('active');
        
        // Focus search if searchable
        if (this.options.searchable) {
            setTimeout(() => {
                this.dropdown.querySelector('.custom-select__search input').focus();
            }, 100);
        }
    }
    
    close() {
        this.dropdown.classList.remove('active');
        this.trigger.classList.remove('active');
        
        // Clear search
        if (this.options.searchable) {
            const searchInput = this.dropdown.querySelector('.custom-select__search input');
            searchInput.value = '';
            this.filterOptions('');
        }
    }
    
    selectOption(optionElement) {
        const value = optionElement.getAttribute('data-value');
        this.setValue(value);
        this.close();
        
        // Trigger change event on original select
        const event = new Event('change', { bubbles: true });
        this.select.dispatchEvent(event);
    }
    
    setValue(value) {
        // Update original select
        this.select.value = value;
        
        // Update custom dropdown display
        const selectedOption = Array.from(this.select.options).find(opt => opt.value === value);
        if (selectedOption) {
            const displayText = selectedOption.text;
            const span = this.trigger.querySelector('span');
            span.textContent = displayText;
            span.classList.remove('custom-select__placeholder');
            
            // Update selected state in custom options
            this.dropdown.querySelectorAll('.custom-select__option').forEach(opt => {
                opt.classList.remove('selected');
                if (opt.getAttribute('data-value') === value) {
                    opt.classList.add('selected');
                }
            });
        }
    }
    
    filterOptions(searchTerm) {
        const options = this.dropdown.querySelectorAll('.custom-select__option');
        const term = searchTerm.toLowerCase();
        
        options.forEach(option => {
            const text = option.textContent.toLowerCase();
            if (text.includes(term)) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });
    }
    
    destroy() {
        this.customSelect.remove();
        this.select.style.display = '';
    }
}

// Initialize all custom dropdowns
function initCustomDropdowns() {
    document.querySelectorAll('.custom-select-wrapper').forEach(wrapper => {
        const searchable = wrapper.hasAttribute('data-searchable');
        const placeholder = wrapper.getAttribute('data-placeholder');
        
        new CustomDropdown(wrapper, {
            searchable,
            placeholder
        });
    });
}

// Auto-initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCustomDropdowns);
} else {
    initCustomDropdowns();
}

// Export for manual initialization
window.CustomDropdown = CustomDropdown;
window.initCustomDropdowns = initCustomDropdowns;