$(document).ready(function () {
    function load_stok_pakan(tgl) {
        $.ajax({
            type: "GET",
            url: "/load_stok_pakan",
            data: {
                tgl: tgl,
            },
            success: function (r) {
                $("#load_stok_pakan").html(r);
                $('[data-bs-toggle="tooltip"]').tooltip();
            },
        });
    }
    load_stok_pakan();

    $(document).on("click", ".opnme_pakan", function () {
        $.ajax({
            type: "get",
            url: "/opname_pakan",
            success: function (r) {
                $("#opname_stk_pkn").html(r);
                $("#opname_pakan").modal("show");
            },
        });
    });
    $(document).on("change", ".tgl_opname", function () {
        var tgl = $(this).val();
        $.ajax({
            type: "get",
            url: "/opname_pakan?tgl=" + tgl,
            success: function (r) {
                $("#opname_stk_pkn").html(r);
            },
        });
    });
    $(document).on("click", ".opnme_vitamin", function () {
        $.ajax({
            type: "get",
            url: "/opnme_vitamin",
            success: function (r) {
                $("#opname_stk_vtmn").html(r);
                $("#opname_vitamin").modal("show");
            },
        });
    });
    $(document).on("change", ".tgl_opname", function () {
        var tgl = $(this).val();
        $.ajax({
            type: "get",
            url: "/opnme_vitamin?tgl=" + tgl,
            success: function (r) {
                $("#opname_stk_vtmn").html(r);
            },
        });
    });
    $(document).on("keyup", ".aktual", function () {
        var count = $(this).attr("count");
        var aktual = $(".aktual" + count).val();
        var stk_program = $(".stk_program" + count).val();

        var selisih = parseFloat(stk_program) - parseFloat(aktual);

        $(".selisih_pakan" + count).text(selisih);
    });
    $(document).on("click", ".history_stok", function () {
        var id_pakan = $(this).attr("id_pakan");
        $.ajax({
            type: "get",
            url: "/history_stok?id_pakan=" + id_pakan,
            success: function (r) {
                $("#history_stk").html(r);
                $("#history_stok").modal("show");
            },
        });
    });
    $(document).on("click", ".tbh_pakan", function () {
        $.ajax({
            type: "get",
            url: "/tambah_pakan",
            success: function (r) {
                $("#tambah_pakan").html(r);
                $(".select").select2();
            },
        });
    });
    $(document).on("click", ".tbh_vitamin", function () {
        $.ajax({
            type: "get",
            url: "/tambah_vitamin",
            success: function (r) {
                $("#tambah_pakan").html(r);
                $(".select").select2();
            },
        });
    });
    $(document).on("submit", "#search_history_stk", function (e) {
        e.preventDefault();
        var tgl1 = $("#tgl1").val();
        var tgl2 = $("#tgl2").val();
        var id_pakan = $("#id_pakan").val();
        $.ajax({
            type: "get",
            url:
                "/history_stok?tgl1=" +
                tgl1 +
                "&tgl2=" +
                tgl2 +
                "&id_pakan=" +
                id_pakan,
            success: function (data) {
                $("#history_stk").html(data);
            },
        });
    });
    var count = 2;
    $(document).on("click", ".tbh_baris", function () {
        count = count + 1;
        $.ajax({
            url: "/tambah_baris_stok?count=" + count,
            type: "Get",
            success: function (data) {
                $("#tb_baris_produk").append(data);
                $(".select").select2();
            },
        });
    });
    var count = 2;
    $(document).on("click", ".tbh_baris_vitamin", function () {
        count = count + 1;
        $.ajax({
            url: "/tambah_baris_stok_vitamin?count=" + count,
            type: "Get",
            success: function (data) {
                $("#tb_baris_produk").append(data);
                $(".select").select2();
            },
        });
    });

    $(document).on("click", ".remove_baris", function () {
        var delete_row = $(this).attr("count");
        $(".baris" + delete_row).remove();
    });

    $(document).on("submit", "#view_baru_pakan", function (e) {
        e.preventDefault();
        var tgl = $(".tgl_view_baru").val();
        load_stok_pakan(tgl);
        $("#viewnew").modal("hide");
    });
});
