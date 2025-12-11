# **PlanX - Interaktívny správca firemných pobočiek**

**Opis:** Navrhovaný systém slúži na správu pobočiek, zamestnancov a
zdrojov v rámci organizácie. Prístup k funkcionalitám je riadený podľa
používateľských rolí, aby bola zabezpečená bezpečnosť a integrita
údajov. Cieľom systému je prehľadná administrácia, sledovanie aktivít a
vizualizácia pobočiek v rámci Slovenska.

### **Funkčné požiadavky (FR)**

#### **1. Registrácia používateľov iba cez administrátora**

Registrácia nových používateľov prebieha výhradne prostredníctvom
administrátora. Bežný používateľ nemá možnosť vytvoriť si účet
samostatne cez rozhranie systému. Administrátor určuje používateľské
role počas vytvárania účtu.\
\
**Akceptačné kritériá:**

- Systém neumožní samostatnú registráciu používateľov.

- Nový používateľ sa vytvára výhradne cez rozhranie administrátora.

- Po vytvorení účtu sa automaticky priradí rola (admin, vedúci,
  používateľ).

#### **2. Administrátor má plný prístup (CRUD) ku všetkým entitám**

Administrátor spravuje celý systém a má oprávnenie vykonávať všetky
operácie nad entitami. Môže vytvárať, upravovať, mazať a prezerať
záznamy vo všetkých častiach systému. Tento prístup zaručuje úplnú
kontrolu nad údajmi.\
\
**Akceptačné kritériá:**

- Administrátor má prístup ku všetkým modulom systému.

- Môže vytvárať, čítať, aktualizovať a mazať údaje pre každú entitu.

- Test CRUD akcií potvrdí funkčnosť v rámci všetkých entít.

#### **3. Vedúci pobočky má prístup iba k údajom svojej pobočky**

Vedúci pobočky po prihlásení vidí a spravuje iba údaje svojej pobočky.
Má možnosť upravovať informácie o pobočke a zamestnancoch, ktorí pod ňu
patria. Zároveň je mu zablokovaný prístup k údajom ostatných pobočiek.

**Akceptačné kritériá:**

- Vedúci pobočky vidí len svoju pobočku a jej zamestnancov.

- Pokus o prístup k cudzej pobočke systém odmietne.

- Vedúci môže meniť údaje o pobočke, adrese, otváracích hodinách a
  zamestnancoch.

#### **4. Bežný používateľ má iba čítací prístup (view)**

Bežný používateľ má v systéme prístup len na čítanie. Môže prezerať
informácie o pobočkách, zamestnancoch a zdrojoch, ale nemôže ich
upravovať. Zabezpečuje sa tým integrita údajov a obmedzenie zásahov do
databázy.

**Akceptačné kritériá:**

- Používateľ nemá prístup k akciám „Pridať", „Upraviť", „Zmazať".

- Pokus o úpravu údajov vygeneruje chybové hlásenie.

- Všetky údaje sú pre neho len na čítanie.

#### **5. Sledovanie poznámok o pobočkách a aktivitách**

Systém zaznamenáva poznámky o všetkých aktivitách, ktoré prebehli v
rámci správy pobočiek. Každá úprava údajov sa uloží ako poznámka s
autorom a časovou pečiatkou. Tým sa umožní sledovanie histórie a
zodpovednosti používateľov.

**Akceptačné kritériá:**

- Každá zmena údajov generuje novú poznámku.

- Poznámky sú dostupné pre administrátora a vedúceho danej pobočky.

- Možnosť filtrovať poznámky podľa pobočky, používateľa alebo dátumu.

#### **6. Možnosť editovať adresu, otváracie hodiny a vedúceho prevádzky**

Administrátor a vedúci pobočky môžu upravovať základné informácie o
prevádzke. Údaje o adrese, otváracích hodinách a vedúcom sa ukladajú do
databázy s históriou zmien. Zmena vedúceho má dopad na prístupové práva,
ktoré manuálne upraví administrátor.

**Akceptačné kritériá:**

- Možnosť editácie adresy, otváracích hodín a vedúceho v rozhraní.

- Po zmene vedúceho sa aktualizujú jeho oprávnenia.

- Každá úprava sa zaznamená ako poznámka.

#### **7. Správa zamestnancov pobočiek vrátane počtu a zamerania**

Systém umožňuje pridávať, upravovať a mazať zamestnancov v rámci
konkrétnej pobočky. Každý zamestnanec má priradené zameranie a patrí pod
vedúceho. Počet zamestnancov sa automaticky zobrazuje v detaile pobočky.

**Akceptačné kritériá:**

- Administrátor a vedúci môžu vykonávať CRUD operácie nad zamestnancami.

- Počet zamestnancov sa po každej zmene aktualizuje automaticky.

- Každý zamestnanec má priradené zameranie a pobočku.

#### **8. CRUD operácie pre všetky hlavné entity**

Systém podporuje CRUD operácie pre všetky hlavné entity vrátane pobočky,
zamestnanca, zdrojov a poznámky. Všetky zmeny sa odrážajú v databáze a
sú po refreshi viditeľné v používateľskom rozhraní.

**Akceptačné kritériá:**

- Každá entita má funkčné operácie: Pridať, Zobraziť, Upraviť, Zmazať.

- Úpravy sa správne zapisujú do databázy.

- Po vykonaní akcie sa používateľovi zobrazí potvrdenie o úspechu.

#### **9. Dynamický filter pre prehľad dostupných zdrojov**

Používateľ môže vyhľadávať a filtrovať zdroje podľa viacerých kritérií,
ako je región, zameranie alebo dostupnosť. Filter je responzívny a
aktualizuje výsledky bez obnovovania celej stránky. Uľahčuje to
vyhľadávanie v rámci veľkého množstva údajov.

**Akceptačné kritériá:**

- Filter je dostupný všetkým používateľom s prístupom k prehľadu.

- Výsledky sa aktualizujú okamžite po zmene filtra.

- Filtrovanie funguje kombinovane (viacero podmienok naraz).

#### **10. Vizualizácia pobočiek na mape Slovenska**

Pobočky sú vizuálne znázornené na interaktívnej mape Slovenska. Po
kliknutí na pobočku sa zobrazia jej detaily vrátane adresy, otváracích
hodín a vedúceho. Používateľ môže mapu priblížiť, oddialiť a pohybovať
sa po nej.

**Akceptačné kritériá:**

- Mapa zobrazuje všetky existujúce pobočky s ich polohou.

- Po kliknutí na pobočku sa zobrazia jej podrobné informácie.

- Funkcie zoom a posun mapy sú plne funkčné.

### **Nefunkčné požiadavky (NR)**

#### **1. Webová aplikácia s responzívnym dizajnom**

Aplikácia musí byť prístupná cez webový prehliadač a nevyžaduje
inštaláciu. Rozhranie sa automaticky prispôsobí rôznym zariadeniam, ako
sú počítače, tablety a mobilné telefóny. Všetky funkcie musia byť
rovnako dostupné a použiteľné bez ohľadu na veľkosť obrazovky.

#### **2. Prehľadné a intuitívne používateľské rozhranie**

Používateľské prostredie musí byť jasne štruktúrované, logicky
usporiadané a jednoduché na používanie. Rozhranie má byť vhodné aj pre
menej technicky zdatných používateľov. Cieľom je umožniť rýchlu
orientáciu a jednoduchý prístup k hlavným funkciám systému.

***3. Rýchle filtrovanie a vyhľadávanie informácií***

Systém má umožňovať efektívne vyhľadávanie a filtrovanie údajov podľa
rôznych kritérií. Používateľ by mal vedieť rýchlo získať požadované
informácie bez zbytočného čakania. Vyhľadávanie má byť dostupné z
mapovej stránky s vizualizáciou pobočiek.

#### **4. Dôraz na bezpečnosť prístupov**

Systém musí obsahovať mechanizmy na kontrolu prístupu podľa
používateľských rolí. Každá rola (administrátor, vedúci, používateľ) má
mať priradené špecifické oprávnenia. Bezpečnostná architektúra má
zabrániť neoprávnenému prístupu a manipulácii s údajmi.

#### **5. Spoľahlivé uloženie dát v databáze**

Dáta musia byť trvalo a konzistentne uložené v databáze. Systém musí
zabezpečiť, aby boli všetky zmeny okamžite uložené a dostupné ostatným
používateľom. Dôraz sa kladie na spoľahlivosť, aktuálnosť a integritu
údajov.
