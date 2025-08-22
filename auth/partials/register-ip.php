<h3 class="section-title">Данные индивидуального предпринимателя</h3>
<div class="form-row">
    <div class="form-group">
        <label for="inn" class="form-label">ИНН *</label>
        <input type="text" id="inn" name="inn" class="form-control" 
               placeholder="12 цифр" pattern="\d{12}" maxlength="12" required
               onblur="validateInn(this)">
        <div class="validation-error" id="inn-error"></div>
    </div>
    <div class="form-group">
        <label for="ogrnip" class="form-label">ОГРНИП *</label>
        <input type="text" id="ogrnip" name="ogrnip" class="form-control" 
               placeholder="15 цифр" pattern="\d{15}" maxlength="15" required
               onblur="validateOgrn(this)">
        <div class="validation-error" id="ogrnip-error"></div>
    </div>
</div>