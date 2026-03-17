-- Seed base data for Fanta W2D (malus only)

-- Malus types
insert into public.malus_types (code, name, amount, is_active)
values
  ('malus-1', 'Malus #1', 0.50, true),
  ('malus-2', 'Malus #2', 1.00, true),
  ('malus-3', 'Extra Malus', 2.00, true)
on conflict (code) do nothing;

-- Users
insert into public.users (username, display_name, password_text, role, is_hidden)
values
  ('admin', 'Administrator', 'admin123', 'admin', true),
  ('andreacariglia', 'Andrea Cariglia', 'user123', 'user', false),
  ('andreadarra', 'Andrea Darra', 'user123', 'user', false),
  ('andreamattei', 'Andrea Mattei', 'user123', 'user', false),
  ('anselmoacquah', 'Anselmo Acquah', 'user123', 'user', false),
  ('beatricemotterle', 'Beatrice Motterle', 'user123', 'user', false),
  ('cynthiachaplin', 'Cynthia Chaplin', 'user123', 'user', false),
  ('davidezanella', 'Davide Zanella', 'user123', 'user', false),
  ('elenavoloshina', 'Elena Voloshina', 'user123', 'user', false),
  ('elenazilotova', 'Elena Zilotova', 'user123', 'user', false),
  ('federicozocca', 'Federico Zocca', 'user123', 'user', false),
  ('giorgiarangoni', 'Giorgia Rangoni', 'user123', 'user', false),
  ('karlaravagnolo', 'Karla Ravagnolo', 'user123', 'user', false),
  ('manuelaclarizia', 'Manuela Clarizia', 'user123', 'user', false),
  ('marcogandini', 'Marco Gandini', 'user123', 'user', false),
  ('marinalovato', 'Marina Lovato', 'user123', 'user', false),
  ('michelaguerra', 'Michela Guerra', 'user123', 'user', false),
  ('miriamferrari', 'Miriam Ferrari', 'user123', 'user', false),
  ('rozazharmukhambetova', 'Roza Zharmukhambetova', 'user123', 'user', false),
  ('richardhough', 'Richard Hough', 'user123', 'user', false),
  ('saralacagnina', 'Sara La Cagnina', 'user123', 'user', false),
  ('sarazambon', 'Sara Zambon', 'user123', 'user', false),
  ('simonegallo', 'Simone Gallo', 'user123', 'user', false),
  ('valeriabianchin', 'Valeria Bianchin', 'user123', 'user', false),
  ('veronicapimazzon', 'Veronica Pimazzon', 'user123', 'user', false)
on conflict (username) do nothing;

-- User malus rules
insert into public.user_malus_rules (user_id, malus_type_id, description)
values
  ((select id from public.users where display_name = 'Andrea Cariglia'),
   (select id from public.malus_types where code = 'malus-1'),
   'If Paglialunga talks to him'),

  ((select id from public.users where display_name = 'Andrea Darra'),
   (select id from public.malus_types where code = 'malus-1'),
   'If he takes random pics of any colleagues'),
  ((select id from public.users where display_name = 'Andrea Darra'),
   (select id from public.malus_types where code = 'malus-2'),
   'If he starts talking about random things (philosophical, historical, sociological, political)'),
  ((select id from public.users where display_name = 'Andrea Darra'),
   (select id from public.malus_types where code = 'malus-3'),
   'If in the middle of the conversation, he leaves but keeps the conversation going from another room/location'),

  ((select id from public.users where display_name = 'Andrea Mattei'),
   (select id from public.malus_types where code = 'malus-1'),
   'If any woman or man flirts with him'),

  ((select id from public.users where display_name = 'Anselmo Acquah'),
   (select id from public.malus_types where code = 'malus-1'),
   'If he eats anything from the reparto surgelati'),
  ((select id from public.users where display_name = 'Anselmo Acquah'),
   (select id from public.malus_types where code = 'malus-2'),
   'If he loses his phone or glasses'),

  ((select id from public.users where display_name = 'Beatrice Motterle'),
   (select id from public.malus_types where code = 'malus-1'),
   'If she sings'),
  ((select id from public.users where display_name = 'Beatrice Motterle'),
   (select id from public.malus_types where code = 'malus-2'),
   'If Stevie says trust me when planning for a podcast series'),

  ((select id from public.users where display_name = 'Cynthia Chaplin'),
   (select id from public.malus_types where code = 'malus-1'),
   'If he/she nods while saying "mmh" or "mhh."'),

  ((select id from public.users where display_name = 'Davide Zanella'),
   (select id from public.malus_types where code = 'malus-1'),
   'If he sleeps in the afternoon'),

  ((select id from public.users where display_name = 'Federico Zocca'),
   (select id from public.malus_types where code = 'malus-1'),
   'If he says "dimmi"'),
  ((select id from public.users where display_name = 'Federico Zocca'),
   (select id from public.malus_types where code = 'malus-3'),
   'If he has to take any institutional photos (Zoppas, Ministro, VF)'),

  ((select id from public.users where display_name = 'Giorgia Rangoni'),
   (select id from public.malus_types where code = 'malus-1'),
   'If she says "Zio Can" or "Mona" or "Fra"'),

  ((select id from public.users where display_name = 'Karla Ravagnolo'),
   (select id from public.malus_types where code = 'malus-1'),
   'If she says "Cute" or "Fuah" or "Daaaamn" or "Girrrl"'),

  ((select id from public.users where display_name = 'Manuela Clarizia'),
   (select id from public.malus_types where code = 'malus-1'),
   'If she calls any andrea 3 times in a row screaming'),
  ((select id from public.users where display_name = 'Manuela Clarizia'),
   (select id from public.malus_types where code = 'malus-2'),
   'If she has to modify a transfer'),
  ((select id from public.users where display_name = 'Manuela Clarizia'),
   (select id from public.malus_types where code = 'malus-3'),
   'If somebody calls her Clarizia'),

  ((select id from public.users where display_name = 'Marina Lovato'),
   (select id from public.malus_types where code = 'malus-1'),
   'If Stevie says she is "la memoria storica/pilastro"'),

  ((select id from public.users where display_name = 'Miriam Ferrari'),
   (select id from public.malus_types where code = 'malus-1'),
   'If she mentions any words in venetian dialect'),

  ((select id from public.users where display_name = 'Roza Zharmukhambetova'),
   (select id from public.malus_types where code = 'malus-1'),
   'If Stevie says trust me when planning for a podcast series'),

  ((select id from public.users where display_name = 'Richard Hough'),
   (select id from public.malus_types where code = 'malus-1'),
   'If he changes his hat'),

  ((select id from public.users where display_name = 'Simone Gallo'),
   (select id from public.malus_types where code = 'malus-1'),
   'If he touches his beard'),
  ((select id from public.users where display_name = 'Simone Gallo'),
   (select id from public.malus_types where code = 'malus-2'),
   'If he "air plays" the drums'),

  ((select id from public.users where display_name = 'Veronica Pimazzon'),
   (select id from public.malus_types where code = 'malus-1'),
   'If she says "mammacara"')
on conflict do nothing;
