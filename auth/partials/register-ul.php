<h3 class="section-title">Данные юридического лица</h3>
<div class="form-row">
    <div class="form-group">
        <label for="director_name" class="form-label">ФИО руководителя *</label>
        <input type="text" id="director_name" name="director_name" class="form-control" 
               placeholder="Иванов Иван Иванович" required>
    </div>
    <div class="form-group">
        <label for="inn" class="form-label">ИНН *</label>
        <input type="text" id="inn" name="inn" class="form-control" 
               placeholder="10 цифр" pattern="\d{10}" maxlength="10" required
               onblur="validateInn(this)">
        <div class="validation-error" id="inn-error"></div>
    </div>
</div>
<div class="form-row">
    <div class="form-group">
        <label for="kpp" class="form-label">КПП *</label>
        <input type="text" id="kpp" name="kpp" class="form-control" 
               placeholder="9 цифр" pattern="\d{9}" maxlength="9" required
               onblur="validateKpp(this)">
        <div class="validation-error" id="kpp-error"></div>
    </div>
    <div class="form-group">
        <label for="ogrn" class="form-label">ОГРН *</label>
        <input type="text" id="ogrn" name="ogrn" class="form-control" 
               placeholder="13 цифр" pattern="\d{13}" maxlength="13" required
               onblur="validateOgrn(this)">
        <div class="validation-error" id="ogrn-error"></div>
    </div>
</div>