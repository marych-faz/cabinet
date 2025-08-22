<h3 class="section-title">Данные физического лица</h3>
<div class="form-row">
    <div class="form-group">
        <label for="birthday" class="form-label">Дата рождения *</label>
        <input type="date" id="birthday" name="birthday" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="inn" class="form-label">ИНН *</label>
        <input type="text" id="inn" name="inn" class="form-control" 
               placeholder="12 цифр" pattern="\d{12}" maxlength="12" required
               onblur="validateInn(this)">
        <div class="validation-error" id="inn-error"></div>
    </div>
</div>
<div class="form-group">
    <label for="document_details" class="form-label">Реквизиты документа (паспорта) *</label>
    <textarea id="document_details" name="document_details" class="form-control" 
              placeholder="Серия, номер, кем и когда выдан" required></textarea>
</div>