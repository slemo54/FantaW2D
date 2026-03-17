export const formatCurrency = (amount) =>
  new Intl.NumberFormat("it-IT", {
    style: "currency",
    currency: "EUR",
  }).format(amount);

const userSeeds = [
  ["admin", "Administrator", "admin", "admin123", "admin@example.com"],
  ["andreacariglia", "Andrea Cariglia", "user", "user123", ""],
  ["andreadarra", "Andrea Darra", "user", "user123", ""],
  ["andreamattei", "Andrea Mattei", "user", "user123", ""],
  ["anselmoacquah", "Anselmo Acquah", "user", "user123", ""],
  ["beatricemotterle", "Beatrice Motterle", "user", "user123", ""],
  ["cynthiachaplin", "Cynthia Chaplin", "user", "user123", ""],
  ["davidezanella", "Davide Zanella", "user", "user123", ""],
  ["elenavoloshina", "Elena Voloshina", "user", "user123", ""],
  ["elenazilotova", "Elena Zilotova", "user", "user123", ""],
  ["federicozocca", "Federico Zocca", "user", "user123", ""],
  ["giorgiarangoni", "Giorgia Rangoni", "user", "user123", ""],
  ["karlaravagnolo", "Karla Ravagnolo", "user", "user123", ""],
  ["manuelaclarizia", "Manuela Clarizia", "user", "user123", ""],
  ["marcogandini", "Marco Gandini", "user", "user123", ""],
  ["marinalovato", "Marina Lovato", "user", "user123", ""],
  ["michelaguerra", "Michela Guerra", "user", "user123", ""],
  ["miriamferrari", "Miriam Ferrari", "user", "user123", ""],
  ["rozazharmukhambetova", "Roza Zharmukhambetova", "user", "user123", ""],
  ["richardhough", "Richard Hough", "user", "user123", ""],
  ["saralacagnina", "Sara La Cagnina", "user", "user123", ""],
  ["sarazambon", "Sara Zambon", "user", "user123", ""],
  ["simonegallo", "Simone Gallo", "user", "user123", ""],
  ["valeriabianchin", "Valeria Bianchin", "user", "user123", ""],
  ["veronicapimazzon", "Veronica Pimazzon", "user", "user123", ""],
];

const ruleSeeds = {
  "Andrea Cariglia": [["malus-1", "If Paglialunga talks to him"]],
  "Andrea Darra": [
    ["malus-1", "If he takes random pics of any colleagues"],
    [
      "malus-2",
      "If he starts talking about random things (philosophical, historical, sociological, political)",
    ],
    [
      "malus-3",
      "If in the middle of the conversation, he leaves but keeps the conversation going from another room/location",
    ],
  ],
  "Andrea Mattei": [["malus-1", "If any woman or man flirts with him"]],
  "Anselmo Acquah": [
    ["malus-1", "If he eats anything from the reparto surgelati"],
    ["malus-2", "If he loses his phone or glasses"],
  ],
  "Beatrice Motterle": [
    ["malus-1", "If she sings"],
    ["malus-2", "If Stevie says trust me when planning for a podcast series"],
  ],
  "Cynthia Chaplin": [['malus-1', 'If he/she nods while saying "mmh" or "mhh."']],
  "Davide Zanella": [["malus-1", "If he sleeps in the afternoon"]],
  "Federico Zocca": [
    ['malus-1', 'If he says "dimmi"'],
    ["malus-3", "If he has to take any institutional photos (Zoppas, Ministro, VF)"],
  ],
  "Giorgia Rangoni": [['malus-1', 'If she says "Zio Can" or "Mona" or "Fra"']],
  "Karla Ravagnolo": [['malus-1', 'If she says "Cute" or "Fuah" or "Daaaamn" or "Girrrl"']],
  "Manuela Clarizia": [
    ["malus-1", "If she calls any andrea 3 times in a row screaming"],
    ["malus-2", "If she has to modify a transfer"],
    ["malus-3", "If somebody calls her Clarizia"],
  ],
  "Marina Lovato": [['malus-1', 'If Stevie says she is "la memoria storica/pilastro"']],
  "Miriam Ferrari": [["malus-1", "If she mentions any words in venetian dialect"]],
  "Roza Zharmukhambetova": [["malus-1", "If Stevie says trust me when planning for a podcast series"]],
  "Richard Hough": [["malus-1", "If he changes his hat"]],
  "Simone Gallo": [
    ["malus-1", "If he touches his beard"],
    ['malus-2', 'If he "air plays" the drums'],
  ],
  "Veronica Pimazzon": [['malus-1', 'If she says "mammacara"']],
};

export const createInitialState = () => {
  let nextRuleId = 1;

  const users = userSeeds.map(([username, displayName, role, password, email], index) => ({
    id: index + 1,
    username,
    displayName,
    role,
    password,
    email,
    balance: 0,
    isHidden: false,
    malusRules: (ruleSeeds[displayName] || []).map(([malusTypeId, description]) => ({
      id: nextRuleId++,
      malusTypeId,
      description,
    })),
  }));

  const sampleTransactions = [
    {
      id: 1,
      userId: 3,
      createdBy: 1,
      type: "malus",
      malusTypeId: "malus-2",
      amount: 1,
      description:
        "If he starts talking about random things (philosophical, historical, sociological, political)",
      cancelled: false,
      timestamp: new Date("2026-03-01T09:15:00").toISOString(),
    },
    {
      id: 2,
      userId: 11,
      createdBy: 2,
      type: "malus",
      malusTypeId: "malus-1",
      amount: 0.5,
      description: 'If he says "dimmi"',
      cancelled: false,
      timestamp: new Date("2026-03-04T14:40:00").toISOString(),
    },
    {
      id: 3,
      userId: 23,
      createdBy: 1,
      type: "malus",
      malusTypeId: "malus-2",
      amount: 1,
      description: 'If he "air plays" the drums',
      cancelled: true,
      timestamp: new Date("2026-03-07T11:20:00").toISOString(),
    },
  ];

  return {
    currentUserId: null,
    currentView: "dashboard",
    users,
    transactions: sampleTransactions,
    proposals: [],
    malusTypes: [
      { id: "malus-1", name: "Malus #1", amount: 0.5 },
      { id: "malus-2", name: "Malus #2", amount: 1 },
      { id: "malus-3", name: "Extra Malus", amount: 2 },
    ],
    nextIds: {
      user: users.length + 1,
      transaction: sampleTransactions.length + 1,
      rule: nextRuleId,
      malusType: 4,
      proposal: 1,
    },
  };
};

export const deriveState = (rawState) => {
  const transactions = [...rawState.transactions].sort(
    (a, b) => new Date(b.timestamp).getTime() - new Date(a.timestamp).getTime(),
  );

  const users = rawState.users.map((user) => {
    const balance = transactions
      .filter((transaction) => transaction.userId === user.id && !transaction.cancelled)
      .reduce((total, transaction) => total - Number(transaction.amount || 0), 0);

    return {
      ...user,
      balance,
      malusRules: [...(user.malusRules || [])].sort((a, b) => a.id - b.id),
    };
  });

  return {
    ...rawState,
    users,
    transactions,
  };
};
