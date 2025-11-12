/**
 * Global Fix para Bootstrap Modal Backdrop Issues
 * Soluciona problemas de modales huérfanos y backdrops bloqueantes
 */

// ✅ Al cargar la página, limpiar cualquier modal o backdrop abierto
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 [GlobalFix] Inicializando limpieza de backdrops...');
    
    // Cerrar todos los modales abiertos
    document.querySelectorAll('.modal.show').forEach(modal => {
        console.log('❌ Cerrando modal abierto:', modal.id);
        const bsModal = bootstrap.Modal.getInstance(modal);
        if(bsModal) {
            bsModal.hide();
        } else {
            modal.classList.remove('show');
        }
    });

    // Remover backdrops huérfanos
    document.querySelectorAll('.modal-backdrop').forEach((backdrop, index) => {
        console.log(`🗑️ Removiendo backdrop #${index + 1}`);
        backdrop.remove();
    });

    // Restaurar estado del body
    document.body.classList.remove('modal-open');
    document.body.style.overflow = 'auto';
    document.body.style.paddingRight = '0px';
    document.body.style.pointerEvents = 'auto';

    console.log('✅ [GlobalFix] Limpieza completada');
});

// ✅ Escuchar cambios de URL (para SPAs o navegación AJAX)
window.addEventListener('popstate', function() {
    console.log('🔄 Detectado cambio de página, limpiando backdrops...');
    setTimeout(() => {
        document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
            backdrop.remove();
        });
        document.body.classList.remove('modal-open');
        document.body.style.overflow = 'auto';
    }, 100);
});

// ✅ FAILSAFE: Si después de 2 segundos todavía hay backdrop, removerlo forzadamente
window.addEventListener('load', function() {
    setTimeout(() => {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        if(backdrops.length > 0) {
            console.warn('⚠️ [GlobalFix] Encontrados backdrops huérfanos, removiendo...');
            backdrops.forEach(backdrop => {
                backdrop.remove();
            });
            document.body.classList.remove('modal-open');
            document.body.style.overflow = 'auto';
        }
    }, 2000);
});

// ✅ Interceptar errores de JavaScript que puedan dejar modales en mal estado
window.addEventListener('error', function(event) {
    console.error('❌ Error detectado:', event.message);
    // No hacer nada, solo loguear
}, false);

// ✅ Agregar método auxiliar global para cerrar cualquier modal
window.closeAllModals = function() {
    console.log('🔒 Cerrando TODOS los modales...');
    
    // Cerrar con Bootstrap
    document.querySelectorAll('.modal.show').forEach(modal => {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if(bsModal) bsModal.hide();
    });

    // Remover backdrops
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
        backdrop.remove();
    });

    // Limpiar estado
    document.body.classList.remove('modal-open');
    document.body.style.overflow = 'auto';
    document.body.style.paddingRight = '0px';
    
    console.log('✅ Todos los modales cerrados');
};

console.log('📦 [GlobalFix] Cargado correctamente');
