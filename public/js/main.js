/**
 * JavaScript principal para VetAlmacén
 * Funcionalidades generales del sistema
 * Fecha: 2026-01-23
 */

(function() {
    'use strict';

    // ========================================
    // Inicialización al cargar el DOM
    // ========================================
    document.addEventListener('DOMContentLoaded', function() {
        initTooltips();
        initPopovers();
        autoHideAlerts();
        initTableSearch();
        initConfirmDialogs();
        initNumberFormatting();
        initDateFormatting();
        initImagePreviews();
        initFormValidations();
        initAjaxFunctions();
    });

    // ========================================
    // Inicializar Tooltips de Bootstrap
    // ========================================
    function initTooltips() {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    }

    // ========================================
    // Inicializar Popovers de Bootstrap
    // ========================================
    function initPopovers() {
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
        [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
    }

    // ========================================
    // Auto-ocultar alertas/toasts
    // ========================================
    function autoHideAlerts() {
        const toasts = document.querySelectorAll('.toast');
        toasts.forEach(toast => {
            const bsToast = new bootstrap.Toast(toast, {
                autohide: true,
                delay: 5000
            });
            bsToast.show();
            
            // Auto hide después de 5 segundos
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        });
    }

    // ========================================
    // Búsqueda en tablas (genérica)
    // ========================================
    function initTableSearch() {
        const searchInputs = document.querySelectorAll('[data-table-search]');
        
        searchInputs.forEach(input => {
            const tableId = input.getAttribute('data-table-search');
            const table = document.getElementById(tableId);
            
            if (!table) return;
            
            input.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
                
                // Mostrar mensaje si no hay resultados
                const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
                if (visibleRows.length === 0 && !table.querySelector('.no-results-row')) {
                    const noResultsRow = document.createElement('tr');
                    noResultsRow.className = 'no-results-row';
                    noResultsRow.innerHTML = `
                        <td colspan="100%" class="text-center py-4 text-muted">
                            <i class="bi bi-search"></i>
                            <p class="mt-2">No se encontraron resultados para "${searchTerm}"</p>
                        </td>
                    `;
                    table.querySelector('tbody').appendChild(noResultsRow);
                } else if (visibleRows.length > 0) {
                    const noResultsRow = table.querySelector('.no-results-row');
                    if (noResultsRow) noResultsRow.remove();
                }
            });
        });
    }

    // ========================================
    // Diálogos de confirmación
    // ========================================
    function initConfirmDialogs() {
        const confirmButtons = document.querySelectorAll('[data-confirm]');
        
        confirmButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const message = this.getAttribute('data-confirm');
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    }

    // ========================================
    // Formateo de números (miles, decimales)
    // ========================================
    function initNumberFormatting() {
        const numberInputs = document.querySelectorAll('input[type="number"][data-format="currency"]');
        
        numberInputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value) {
                    const value = parseFloat(this.value);
                    if (!isNaN(value)) {
                        this.value = value.toFixed(2);
                    }
                }
            });
        });
    }

    // ========================================
    // Formateo de fechas
    // ========================================
    function initDateFormatting() {
        const dateElements = document.querySelectorAll('[data-date-format]');
        
        dateElements.forEach(element => {
            const dateStr = element.textContent.trim();
            const date = new Date(dateStr);
            
            if (!isNaN(date.getTime())) {
                const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
                element.textContent = date.toLocaleDateString('es-PE', options);
            }
        });
    }

    // ========================================
    // Preview de imágenes antes de subir
    // ========================================
    function initImagePreviews() {
        const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
        
        imageInputs.forEach(input => {
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;
                
                // Validar tipo de archivo
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!validTypes.includes(file.type)) {
                    showAlert('Solo se permiten imágenes JPG, JPEG o PNG', 'danger');
                    this.value = '';
                    return;
                }
                
                // Validar tamaño (5MB)
                const maxSize = 5 * 1024 * 1024; // 5MB
                if (file.size > maxSize) {
                    showAlert('La imagen no debe superar los 5MB', 'danger');
                    this.value = '';
                    return;
                }
                
                // Mostrar preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    let previewContainer = document.getElementById('imagePreviewContainer');
                    let previewImg = document.getElementById('imagePreview');
                    
                    if (previewContainer && previewImg) {
                        previewImg.src = e.target.result;
                        previewContainer.style.display = 'block';
                    }
                };
                reader.readAsDataURL(file);
            });
        });
    }

    // ========================================
    // Validaciones de formularios
    // ========================================
    function initFormValidations() {
        const forms = document.querySelectorAll('form[data-validate]');
        
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                    showAlert('Por favor complete todos los campos requeridos correctamente', 'warning');
                }
                
                form.classList.add('was-validated');
            });
        });
    }

    // ========================================
    // Funciones AJAX
    // ========================================
    function initAjaxFunctions() {
        // Cargar subcategorías dinámicamente
        const categoriaSelects = document.querySelectorAll('select#categoria_id');
        categoriaSelects.forEach(select => {
            select.addEventListener('change', function() {
                loadSubcategorias(this.value);
            });
        });
    }

    // ========================================
    // Cargar subcategorías por AJAX
    // ========================================
    function loadSubcategorias(categoriaId) {
        const subcategoriaSelect = document.getElementById('subcategoria_id');
        if (!subcategoriaSelect) return;
        
        if (!categoriaId) {
            subcategoriaSelect.innerHTML = '<option value="">Primero seleccione una categoría</option>';
            subcategoriaSelect.disabled = true;
            return;
        }
        
        // Mostrar loading
        subcategoriaSelect.innerHTML = '<option value="">Cargando...</option>';
        subcategoriaSelect.disabled = true;
        
        fetch(`/vetalmacen/public/index.php?url=productos/getSubcategorias&categoria_id=${categoriaId}`)
            .then(response => response.json())
            .then(data => {
                subcategoriaSelect.innerHTML = '<option value="">Seleccione una subcategoría</option>';
                
                data.forEach(subcategoria => {
                    const option = document.createElement('option');
                    option.value = subcategoria.Id;
                    option.textContent = subcategoria.Nombre;
                    subcategoriaSelect.appendChild(option);
                });
                
                subcategoriaSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error al cargar las subcategorías', 'danger');
                subcategoriaSelect.innerHTML = '<option value="">Error al cargar</option>';
            });
    }

    // ========================================
    // Obtener stock disponible (AJAX)
    // ========================================
    window.getStockDisponible = function(productoId, sucursalId, callback) {
        if (!productoId || !sucursalId) {
            if (callback) callback(0);
            return;
        }
        
        fetch(`/vetalmacen/public/index.php?url=stock/getStockProducto&producto_id=${productoId}&sucursal_id=${sucursalId}`)
            .then(response => response.json())
            .then(data => {
                const stock = data.stock || 0;
                if (callback) callback(stock);
            })
            .catch(error => {
                console.error('Error:', error);
                if (callback) callback(0);
            });
    };

    // ========================================
    // Mostrar alertas dinámicamente
    // ========================================
    function showAlert(message, type = 'info') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3" 
                 role="alert" style="z-index: 9999; min-width: 300px;">
                <i class="bi bi-${getAlertIcon(type)}"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', alertHtml);
        
        // Auto remove después de 5 segundos
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            if (alerts.length > 0) {
                const lastAlert = alerts[alerts.length - 1];
                lastAlert.style.opacity = '0';
                setTimeout(() => lastAlert.remove(), 300);
            }
        }, 5000);
    }

    // ========================================
    // Obtener icono de alerta según tipo
    // ========================================
    function getAlertIcon(type) {
        const icons = {
            'success': 'check-circle-fill',
            'danger': 'exclamation-triangle-fill',
            'warning': 'exclamation-circle-fill',
            'info': 'info-circle-fill'
        };
        return icons[type] || 'info-circle-fill';
    }

    // ========================================
    // Formatear precio en moneda
    // ========================================
    window.formatCurrency = function(amount) {
        return new Intl.NumberFormat('es-PE', {
            style: 'currency',
            currency: 'PEN'
        }).format(amount);
    };

    // ========================================
    // Formatear números con separadores de miles
    // ========================================
    window.formatNumber = function(number, decimals = 0) {
        return new Intl.NumberFormat('es-PE', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(number);
    };

    // ========================================
    // Debounce function (útil para búsquedas)
    // ========================================
    window.debounce = function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };

    // ========================================
    // Copiar al portapapeles
    // ========================================
    window.copyToClipboard = function(text) {
        navigator.clipboard.writeText(text).then(() => {
            showAlert('Copiado al portapapeles', 'success');
        }).catch(err => {
            console.error('Error al copiar:', err);
            showAlert('Error al copiar al portapapeles', 'danger');
        });
    };

    // ========================================
    // Exportar funciones globales
    // ========================================
    window.showAlert = showAlert;

    // ========================================
    // Loading overlay
    // ========================================
    window.showLoading = function(message = 'Cargando...') {
        const loadingHtml = `
            <div id="loadingOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
                 style="background: rgba(0,0,0,0.7); z-index: 99999;">
                <div class="text-center text-white">
                    <div class="spinner-border mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="fs-5">${message}</p>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', loadingHtml);
    };

    window.hideLoading = function() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) overlay.remove();
    };

    // ========================================
    // Validar RUC peruano
    // ========================================
    window.validarRUC = function(ruc) {
        if (!/^\d{11}$/.test(ruc)) return false;
        return true;
    };

    // ========================================
    // Validar email
    // ========================================
    window.validarEmail = function(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    };

    // ========================================
    // Scroll suave a elemento
    // ========================================
    window.scrollToElement = function(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    // ========================================
    // Imprimir página o sección
    // ========================================
    window.printSection = function(sectionId) {
        const section = document.getElementById(sectionId);
        if (!section) return;
        
        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Imprimir</title>');
        printWindow.document.write('<link rel="stylesheet" href="/vetalmacen/public/css/bootstrap.min.css">');
        printWindow.document.write('<link rel="stylesheet" href="/vetalmacen/public/css/styles.css">');
        printWindow.document.write('</head><body>');
        printWindow.document.write(section.innerHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 250);
    };

    // ========================================
    // Confirmar antes de salir con cambios sin guardar
    // ========================================
    let formChanged = false;
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('change', () => {
                formChanged = true;
            });
        });

        form.addEventListener('submit', () => {
            formChanged = false;
        });
    });

    window.addEventListener('beforeunload', (e) => {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    console.log('✅ VetAlmacén - Sistema inicializado correctamente');
})();