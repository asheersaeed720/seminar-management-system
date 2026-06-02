/* University Seminar Management System — Main JS */
$(function () {

    /* ── jQuery Validation defaults ─────────────────────────── */
    $.validator.setDefaults({
        errorClass: 'is-invalid',
        validClass: 'is-valid',
        errorElement: 'div',
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback');
            element.closest('.mb-3, .input-group').append(error);
        },
        highlight: function (element) {
            $(element).addClass('is-invalid').removeClass('is-valid');
        },
        unhighlight: function (element) {
            $(element).removeClass('is-invalid').addClass('is-valid');
        }
    });

    /* ── Registration form ───────────────────────────────────── */
    $('#registrationForm').validate({
        rules: {
            student_name:  { required: true, minlength: 2 },
            student_email: { required: true, email: true },
            student_phone: { required: false, minlength: 7 },
            student_roll:  { required: false }
        },
        messages: {
            student_name:  { required: 'Full name is required', minlength: 'At least 2 characters' },
            student_email: { required: 'Email is required', email: 'Enter a valid email' }
        }
    });

    /* ── Teacher / Admin forms ───────────────────────────────── */
    $('#teacherForm').validate({
        rules: {
            name:       { required: true, minlength: 2 },
            email:      { required: true, email: true },
            password:   { required: $('#teacherForm').data('mode') === 'add', minlength: 6 },
            department: { required: false }
        }
    });

    $('#seminarForm').validate({
        rules: {
            title:         { required: true, minlength: 3 },
            seminar_date:  { required: true },
            seminar_time:  { required: true },
            capacity:      { required: true, min: 1, digits: true }
        }
    });

    $('#loginForm').validate({
        rules: {
            email:    { required: true, email: true },
            password: { required: true, minlength: 4 }
        }
    });

    /* ── AJAX Attendance Toggle ──────────────────────────────── */
    $(document).on('click', '.attendance-toggle', function () {
        var $btn       = $(this);
        var regId      = $btn.data('reg-id');
        var seminarId  = $btn.data('seminar-id');
        var current    = $btn.data('status');
        var newStatus  = (current === 'present') ? 'absent' : 'present';

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url:  BASE_URL + '/ajax/mark_attendance.php',
            type: 'POST',
            data: {
                registration_id: regId,
                seminar_id:      seminarId,
                status:          newStatus,
                csrf_token:      CSRF_TOKEN
            },
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $btn.data('status', newStatus);
                    var badge = newStatus === 'present'
                        ? '<span class="badge bg-success">Present</span>'
                        : '<span class="badge bg-danger">Absent</span>';
                    $btn.prop('disabled', false).html(badge);
                    updateAttendanceSummary(seminarId);
                } else {
                    alert(res.message || 'Error updating attendance.');
                    $btn.prop('disabled', false);
                    restoreBadge($btn, current);
                }
            },
            error: function () {
                alert('Server error. Please try again.');
                $btn.prop('disabled', false);
                restoreBadge($btn, current);
            }
        });
    });

    function restoreBadge($btn, status) {
        var badge = status === 'present'
            ? '<span class="badge bg-success">Present</span>'
            : '<span class="badge bg-danger">Absent</span>';
        $btn.html(badge);
    }

    function updateAttendanceSummary(seminarId) {
        $.get(BASE_URL + '/ajax/mark_attendance.php', { action: 'summary', seminar_id: seminarId }, function (res) {
            if (res.success) {
                $('#att-present').text(res.present);
                $('#att-absent').text(res.absent);
                $('#att-unmarked').text(res.unmarked);
                var pct = res.total > 0 ? Math.round((res.present / res.total) * 100) : 0;
                $('#att-progress').css('width', pct + '%').attr('aria-valuenow', pct).text(pct + '%');
            }
        }, 'json');
    }

    /* ── Delete confirmation ─────────────────────────────────── */
    $(document).on('submit', '.form-delete', function (e) {
        if (!confirm('Are you sure you want to delete this record? This action cannot be undone.')) {
            e.preventDefault();
        }
    });

    /* ── Auto-dismiss alerts ─────────────────────────────────── */
    setTimeout(function () {
        $('.alert-dismissible').alert('close');
    }, 5000);
});

/* Globals injected by PHP pages that need AJAX — provide safe defaults */
if (typeof BASE_URL   === 'undefined') var BASE_URL   = '';
if (typeof CSRF_TOKEN === 'undefined') var CSRF_TOKEN = '';
