import Swal from 'sweetalert2'

export function showConfirmAlert(executeFunc, id, obj) {
    let {title} = obj ? obj : {};
    const swalWithBootstrapButtons = Swal.mixin({
        // confirmButtonClass: 'btn btn-success',
        // cancelButtonClass: 'btn btn-danger',
        // buttonsStyling: false,
    });

    swalWithBootstrapButtons({
        title: title ? title : 'Are you sure?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
    }).then((result) => {
        if (result.value) {
            executeFunc(id);
            // swalWithBootstrapButtons('Success!', '', 'success')
        }
        // else if (
        //     result.dismiss === swalWithBootstrapButtons.DismissReason.cancel
        // ) {
        //     swalWithBootstrapButtons('Cancelled', 'Your imaginary file is safe :)', 'error')
        // }
    });
}

