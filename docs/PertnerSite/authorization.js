// Загрузка данных из localStorage при загрузке страницы
    window.addEventListener('load', () => {
        const savedUser = localStorage.getItem('edtUser');
        const savedPass = localStorage.getItem('edtPass');
  
        if (savedUser) document.getElementById('edtUser').value = savedUser;
        if (savedPass) document.getElementById('edtPass').value = savedPass;
      });
  
      // Обработка входа
      document.getElementById('loginForm').addEventListener('submit', function (e) {
        e.preventDefault();
  
        const username = document.getElementById('edtUser').value.trim();
        const password = document.getElementById('edtPass').value.trim();
  
        // Сохраняем данные в браузере
        if (username) localStorage.setItem('edtUser', username);
        if (password) localStorage.setItem('edtPass', password);

        // Переход на проверучную функцию
        // handleLogin();
      });
      // Проверить 
      function handleLogin() {
        // Проверка на правильность входа в программу
        let user;
        if(user = localCheckUser(document.getElementById('edtUser').value,document.getElementById('edtPass').value)) {
            // смотрит нашел ли он пользователя вообще
            if(user) { 
              if(user.is_admin) window.location.href = "./admin.html";
              else window.location.href = "./partner.html";
            }
          else alert("Ошибка пользователя или пароля!");
         }
      }
  
      function handleClose() {
        if (confirm("Вы действительно хотите выйти из программы?")) window.close();
      }
      // Эта функция временная после присоединения backend убрать !!!!!!
      function localCheckUser(login, pass) {
        let Users = [
          {
            "id": 1,
            "is_admin": 1,
            "login": "Admin",
            "email": "admin@gmail.com",
            "pass": "1111",
            "name": "Королева Алевтина Ивановна",
            "dog_num": "123/456",
            "dog_beg_date": "01.01.2025",
            "dog_end_date": "31.12.2025",
            "phone": "+7(927)785-22-55",
            "bank_name": "АКБ «Абсолют Банк» (ПАО)",
            "bank_bik": "044525976",
            "bank_ks": "30101810500000000976",
            "bank_rs": "40817810099910004312"
        },
        {
            "id": 2,
            "is_admin": 0,
            "login": "User",
            "email": "user@gmail.com",
            "pass": "1111",
            "name": "Большаков Давид Тихонович",
            "dog_num": "321/456",
            "dog_beg_date": "01.01.2025",
            "dog_end_date": "31.12.2025",
            "phone": "+7(917)532-45-12",
            "bank_name": "ПАО «АК БАРС» БАНК",
            "bank_bik": "049205805",
            "bank_ks": "30101810000000000805",
            "bank_rs": "40702810680060657001"
        },
        {
            "id": 3,
            "is_admin": 0,
            "login": "IvanovI",
            "email": "ivanov@mail.ru",
            "pass": "qwerty",
            "name": "Иванов Иван Сергеевич",
            "dog_num": "789/123",
            "dog_beg_date": "15.03.2025",
            "dog_end_date": "15.03.2026",
            "phone": "+7(987)654-32-10",
            "bank_name": "ПАО Сбербанк",
            "bank_bik": "044525225",
            "bank_ks": "30101810400000000225",
            "bank_rs": "40817810700012345678"
        },
        {
            "id": 4,
            "is_admin": 0,
            "login": "PetrovaM",
            "email": "petrova@yandex.ru",
            "pass": "123456",
            "name": "Петрова Мария Владимировна",
            "dog_num": "456/789",
            "dog_beg_date": "01.02.2025",
            "dog_end_date": "01.02.2026",
            "phone": "+7(917)123-45-67",
            "bank_name": "ВТБ (ПАО)",
            "bank_bik": "044525187",
            "bank_ks": "30101810700000000187",
            "bank_rs": "40701810000001122334"
        },
        {
            "id": 5,
            "is_admin": 0,
            "login": "SidorovA",
            "email": "sidorov@gmail.com",
            "pass": "asdfgh",
            "name": "Сидоров Алексей Петрович",
            "dog_num": "987/654",
            "dog_beg_date": "10.05.2025",
            "dog_end_date": "10.05.2026",
            "phone": "+7(927)987-65-43",
            "bank_name": "АО «Райффайзенбанк»",
            "bank_bik": "044525700",
            "bank_ks": "30101810200000000700",
            "bank_rs": "40817810500009876543"
        },
        {
            "id": 6,
            "is_admin": 0,
            "login": "KuznetsovaE",
            "email": "kuznetsova@mail.ru",
            "pass": "zxcvbn",
            "name": "Кузнецова Елена Дмитриевна",
            "dog_num": "654/321",
            "dog_beg_date": "20.04.2025",
            "dog_end_date": "20.04.2026",
            "phone": "+7(917)555-44-33",
            "bank_name": "ПАО «Промсвязьбанк»",
            "bank_bik": "044525555",
            "bank_ks": "30101810400000000555",
            "bank_rs": "40701810600001112222"
        },
        {
            "id": 7,
            "is_admin": 0,
            "login": "FedorovD",
            "email": "fedorov@yandex.ru",
            "pass": "password",
            "name": "Федоров Дмитрий Николаевич",
            "dog_num": "222/333",
            "dog_beg_date": "05.01.2025",
            "dog_end_date": "05.01.2026",
            "phone": "+7(927)111-22-33",
            "bank_name": "АО «Тинькофф Банк»",
            "bank_bik": "044525974",
            "bank_ks": "30101810145250000974",
            "bank_rs": "40817810700003334455"
        },
        {
            "id": 8,
            "is_admin": 0,
            "login": "SmirnovaO",
            "email": "smirnova@gmail.com",
            "pass": "olga123",
            "name": "Смирнова Ольга Васильевна",
            "dog_num": "555/666",
            "dog_beg_date": "12.12.2024",
            "dog_end_date": "12.12.2025",
            "phone": "+7(917)999-88-77",
            "bank_name": "ПАО «МОСКОВСКИЙ КРЕДИТНЫЙ БАНК»",
            "bank_bik": "044525092",
            "bank_ks": "30101810300000000092",
            "bank_rs": "40701810200005556677"
        },
        {
            "id": 9,
            "is_admin": 0,
            "login": "NikolaevA",
            "email": "nikolaev@mail.ru",
            "pass": "artem123",
            "name": "Николаев Артем Игоревич",
            "dog_num": "777/888",
            "dog_beg_date": "03.03.2025",
            "dog_end_date": "03.03.2026",
            "phone": "+7(927)333-44-55",
            "bank_name": "ПАО «РОСБАНК»",
            "bank_bik": "044525256",
            "bank_ks": "30101810000000000256",
            "bank_rs": "40702810400007778899"
        },
        {
            "id": 10,
            "is_admin": 0,
            "login": "VolkovaA",
            "email": "volkova@yandex.ru",
            "pass": "anna2025",
            "name": "Волкова Анна Михайловна",
            "dog_num": "999/000",
            "dog_beg_date": "25.06.2025",
            "dog_end_date": "25.06.2026",
            "phone": "+7(917)777-66-55",
            "bank_name": "ПАО «Совкомбанк»",
            "bank_bik": "044525341",
            "bank_ks": "30101810400000000341",
            "bank_rs": "40701810500009990000"
        }        ];
        console.log("Начало работы перебора name:"+login+" pass:"+pass);
        for(const element of Users) {
//          console.log("name:"+Users[i].login+" pass:"+Users[i].pass+" IsAdmin:"+Users[i].is_admin); // удалить после отладки !!!!
          if(element.login===login && element.pass===pass) { 
            sessionStorage.setItem("UserName",element.name);
            sessionStorage.setItem("UserId",element.id);
            sessionStorage.setItem("UserIsAdmin",element.is_admin);
            sessionStorage.setItem("UserLogin",element.login);
            return element;
          }
        };
        return null;
      }
      