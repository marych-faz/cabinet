    <div class="auth-body">
        <?php if (isset($error)): ?>
            <div class="status-message status-error">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="status-message status-success">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($user): ?>
        <form method="post" class="auth-form">
            <input type="hidden" name="action" value="save">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="form-label">ФИО</label>
                    <input type="text" id="name" class="form-control" 
                           value="<?= htmlspecialchars($user['name']) ?>" 
                           readonly>
                </div>
                
                <div class="form-group">
                    <label for="login" class="form-label">Логин</label>
                    <input type="text" id="login" name="login" class="form-control" 
                           value="<?= htmlspecialchars($user['login']) ?>" 
                           required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password" class="form-label">Новый пароль</label>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="Оставьте пустым, если не нужно менять">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Подтверждение пароля</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                           placeholder="Повторите новый пароль">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?= htmlspecialchars($user['email']) ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="phone" class="form-label">Телефон</label>
                    <div class="phone-input-container">
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               value="<?= htmlspecialchars($user['phone']) ?>" 
                               required>
                        <a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $user['phone'])) ?>" class="phone-call">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            
            <h3 class="section-title">Данные договора</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="dog_num" class="form-label">Номер договора</label>
                    <input type="text" id="dog_num" name="dog_num" class="form-control" 
                           value="<?= htmlspecialchars($user['dog_num']) ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="dog_beg_date" class="form-label">Дата начала</label>
                    <input type="date" id="dog_beg_date" name="dog_beg_date" class="form-control" 
                           value="<?= htmlspecialchars($user['dog_beg_date']) ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="dog_end_date" class="form-label">Дата окончания</label>
                    <input type="date" id="dog_end_date" name="dog_end_date" class="form-control" 
                           value="<?= htmlspecialchars($user['dog_end_date']) ?>" 
                           required>
                </div>
            </div>
            
            <h3 class="section-title">Банковские реквизиты</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="bank_bik" class="form-label">БИК</label>
                    <input type="text" id="bank_bik" name="bank_bik" class="form-control" 
                           value="<?= htmlspecialchars($user['bank_bik']) ?>" 
                           required
                           onblur="fetchBankDetails()">
                </div>
                
                <div class="form-group">
                    <label for="bank_name" class="form-label">Наименование банка</label>
                    <input type="text" id="bank_name" name="bank_name" class="form-control" 
                           value="<?= htmlspecialchars($user['bank_name']) ?>" 
                           required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="bank_rs" class="form-label">Расчетный счет</label>
                    <input type="text" id="bank_rs" name="bank_rs" class="form-control" 
                           value="<?= htmlspecialchars($user['bank_rs']) ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="bank_ks" class="form-label">Корреспондентский счет</label>
                    <input type="text" id="bank_ks" name="bank_ks" class="form-control" 
                           value="<?= htmlspecialchars($user['bank_ks']) ?>" 
                           required>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-right: 8px;">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    Сохранить
                </button>
                
                <a href="javascript:history.back()" class="btn btn-secondary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-right: 8px;">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    Выйти
                </a>
            </div>
        </form>
        <?php else: ?>
            <div class="status-message status-error">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                <span>Не удалось загрузить данные пользователя</span>
            </div>
        <?php endif; ?>
    </div>
</div>