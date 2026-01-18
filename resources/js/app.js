import $ from "jquery";
window.$ = window.jQuery = $;

import "./bootstrap";
import Alpine from "alpinejs";
window.Alpine = Alpine;
Alpine.start();
import "datatables.net-dt/css/dataTables.dataTables.css";
import DataTable from "datatables.net-dt";
import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";
window.Swal = Swal;

window.showToast = function (type, message) {
    Swal.fire({
        toast: true,
        position: "top-end",
        icon: type,
        title: message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    });
};

document.addEventListener("DOMContentLoaded", function () {
    const flashMessage = document.getElementById("flash-message");
    if (flashMessage) {
        const type = flashMessage.getAttribute("data-type");
        const message = flashMessage.getAttribute("data-message");
        window.showToast(type, message);
    }
});

$(document).ready(function () {
    $("#usersTable").DataTable({
        responsive: true,
        pageLength: 10,
        lengthChange: false,
        language: {
            searchPlaceholder: "Cari...",
            search: "",
        },
    });
    $("#studentsTable").DataTable({
        responsive: true,
        pageLength: 10,
        lengthChange: false,
        language: {
            searchPlaceholder: "Cari...",
            search: "",
        },
    });
    $("#questionsTable").DataTable({
        responsive: true,
        pageLength: 10,
        lengthChange: false,
        language: {
            searchPlaceholder: "Cari...",
            search: "",
        },
    });

    const examsTableEl = $("#examsTable");
    if (examsTableEl.length) {
        const examsTable = examsTableEl.DataTable({
            responsive: true,
            pageLength: 10,
            lengthChange: false,
            language: {
                searchPlaceholder: "Cari...",
                search: "",
            },
        });

        examsTableEl.on("click", ".js-exam-detail-toggle", function () {
            const button = $(this);
            const examId = button.data("examId");
            if (!examId) {
                return;
            }

            const template = document.getElementById(
                `exam-detail-template-${examId}`
            );
            if (!template) {
                return;
            }

            const tr = button.closest("tr");
            const row = examsTable.row(tr);
            const isOpen = row.child.isShown();

            if (isOpen) {
                row.child.hide();
                tr.removeClass("shown");
                button.text("Detail").attr("aria-expanded", "false");
                return;
            }

            row.child(template.innerHTML).show();
            tr.addClass("shown");
            button.text("Tutup").attr("aria-expanded", "true");
        });
    }
});
