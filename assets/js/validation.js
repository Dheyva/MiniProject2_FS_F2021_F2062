/**
 * validation.js - client-side validation for every form.
 * Forms opt in with data-validate="register|login|category|project".
 * (Server-side PHP validation is still the final authority.)
 */
(function () {
  'use strict';

  var MAX_FILE = 5 * 1024 * 1024;               // 5MB
  var ALLOWED_EXT = ['pdf', 'docx', 'zip'];

  function setError(input, msg) {
    input.classList.add('is-invalid');
    var fb = input.parentNode.querySelector('.invalid-feedback') ||
             input.closest('.mb-3').querySelector('.invalid-feedback');
    if (fb) fb.textContent = msg;
  }
  function clearError(input) {
    input.classList.remove('is-invalid');
    var box = input.closest('.mb-3');
    var fb = box && box.querySelector('.invalid-feedback');
    if (fb) fb.textContent = '';
  }

  var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

  var rules = {
    full_name: function (v) {
      if (!v) return 'Full name is required.';
      if (v.length < 3) return 'Full name must be at least 3 characters.';
      if (v.length > 100) return 'Full name must not exceed 100 characters.';
      if (!/^[A-Za-z@' .\-\/]+$/.test(v)) return 'Full name may contain letters and spaces only.';
      return '';
    },
    email: function (v) {
      if (!v) return 'Email address is required.';
      if (!emailRe.test(v)) return 'Enter a valid email address.';
      return '';
    },
    password_register: function (v) {
      if (!v) return 'Password is required.';
      if (v.length < 8) return 'Password must be at least 8 characters.';
      if (!/[A-Za-z]/.test(v) || !/\d/.test(v)) return 'Password must contain letters and numbers.';
      return '';
    },
    password_login: function (v) { return v ? '' : 'Password is required.'; },
    confirm_password: function (v, form) {
      if (!v) return 'Please confirm your password.';
      if (v !== form.elements['password'].value) return 'Passwords do not match.';
      return '';
    },
    category_name: function (v) {
      if (!v) return 'Category name is required.';
      if (v.length < 3) return 'Category name must be at least 3 characters.';
      if (v.length > 100) return 'Category name must not exceed 100 characters.';
      return '';
    },
    description: function (v, form) {
      var min = form.dataset.validate === 'category' ? 10 : 20;
      if (!v) return 'Description is required.';
      if (v.length < min) return 'Description must be at least ' + min + ' characters.';
      return '';
    },
    title: function (v) {
      if (!v) return 'Project title is required.';
      if (v.length < 3) return 'Project title must be at least 3 characters.';
      if (v.length > 200) return 'Project title must not exceed 200 characters.';
      return '';
    },
    category_id: function (v) { return v ? '' : 'Please select a category.'; },
    tech_stack: function (v) {
      if (!v) return 'Tech stack is required.';
      if (v.length > 255) return 'Tech stack must not exceed 255 characters.';
      return '';
    }
  };

  function fileCheck(input, required) {
    var f = input.files && input.files[0];
    if (!f) return required ? 'Please choose a file to upload.' : '';
    var ext = f.name.split('.').pop().toLowerCase();
    if (ALLOWED_EXT.indexOf(ext) === -1) return 'Only PDF, DOCX or ZIP files are allowed.';
    if (f.size > MAX_FILE) return 'File is too large. Maximum size is 5MB.';
    if (f.size === 0) return 'The selected file is empty.';
    return '';
  }

  function validateField(input, form) {
    var name = input.name, type = form.dataset.validate, msg = '';
    if (input.type === 'file') {
      msg = fileCheck(input, input.dataset.required === '1');
    } else {
      var v = input.value.trim();
      var key = name === 'password' ? (type === 'register' ? 'password_register' : 'password_login') : name;
      if (rules[key]) msg = rules[key](v, form);
    }
    if (msg) { setError(input, msg); return false; }
    clearError(input);
    return true;
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[data-validate]').forEach(function (form) {
      form.setAttribute('novalidate', 'novalidate');
      var fields = form.querySelectorAll('input[name]:not([type=hidden]), textarea, select');

      fields.forEach(function (input) {
        var evt = (input.tagName === 'SELECT' || input.type === 'file') ? 'change' : 'blur';
        input.addEventListener(evt, function () { validateField(input, form); });
        input.addEventListener('input', function () { if (input.classList.contains('is-invalid')) validateField(input, form); });
      });

      form.addEventListener('submit', function (ev) {
        var ok = true, first = null;
        fields.forEach(function (input) {
          if (!validateField(input, form)) { ok = false; first = first || input; }
        });
        if (!ok) { ev.preventDefault(); first.focus(); }
      });
    });
  });
})();
