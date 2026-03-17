import { useEffect, useMemo, useState } from "react";
import { createInitialState, deriveState, formatCurrency } from "./seed";

const STORAGE_KEY = "fantaw2d-react-state-v1";
const SUPABASE_URL = import.meta.env.VITE_SUPABASE_URL;
const SUPABASE_ANON_KEY = import.meta.env.VITE_SUPABASE_ANON_KEY;
let supabaseClient = null;
let supabaseInitTried = false;

const getSupabaseClient = async () => {
  if (supabaseInitTried) {
    return supabaseClient;
  }
  supabaseInitTried = true;

  if (!SUPABASE_URL || !SUPABASE_ANON_KEY) {
    return null;
  }

  try {
    const { createClient } = await import("@supabase/supabase-js");
    supabaseClient = createClient(SUPABASE_URL, SUPABASE_ANON_KEY);
    return supabaseClient;
  } catch (error) {
    console.error("Supabase init error", error);
    return null;
  }
};

const emptyUserForm = {
  displayName: "",
  username: "",
  password: "",
  email: "",
  role: "user",
  isHidden: false,
};

const emptyTransactionForm = {
  userId: "",
  malusTypeId: "",
  description: "",
  ruleId: "",
};

const emptyProposalForm = {
  proposerName: "",
  targetName: "",
  description: "",
};

function App() {
  const [state, setState] = useState(() => {
    const stored = window.localStorage.getItem(STORAGE_KEY);
    if (!stored) {
      return deriveState(createInitialState());
    }

    try {
      return deriveState(JSON.parse(stored));
    } catch {
      return deriveState(createInitialState());
    }
  });
  const [loginForm, setLoginForm] = useState({ username: "", password: "" });
  const [loginError, setLoginError] = useState("");
  const [notice, setNotice] = useState("");
  const [userForm, setUserForm] = useState(emptyUserForm);
  const [editingUserId, setEditingUserId] = useState(null);
  const [adminRuleUserId, setAdminRuleUserId] = useState(state.users.find((user) => user.role !== "admin")?.id ?? 1);
  const [ruleForm, setRuleForm] = useState({ malusTypeId: "", description: "" });
  const [editingRuleId, setEditingRuleId] = useState(null);
  const [malusForm, setMalusForm] = useState({ name: "", amount: "0.50" });
  const [editingMalusId, setEditingMalusId] = useState(null);
  const [transactionForm, setTransactionForm] = useState(emptyTransactionForm);
  const [editingTransactionId, setEditingTransactionId] = useState(null);
  const [proposalForm, setProposalForm] = useState(emptyProposalForm);
  const [voteName, setVoteName] = useState("");
  const [publicProposals, setPublicProposals] = useState([]);
  const [publicLoading, setPublicLoading] = useState(false);
  const [publicError, setPublicError] = useState("");
  const [usersLoading, setUsersLoading] = useState(false);
  const [adminUserFilter, setAdminUserFilter] = useState("active");

  useEffect(() => {
    window.localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
  }, [state]);

  useEffect(() => {
    if (!notice) {
      return undefined;
    }

    const timeout = window.setTimeout(() => setNotice(""), 3000);
    return () => window.clearTimeout(timeout);
  }, [notice]);

  const currentUser = state.users.find((user) => user.id === state.currentUserId) ?? null;
  const isAdmin = currentUser?.role === "admin";
  const visibleUsers = state.users.filter((user) => !user.isHidden && user.role !== "admin");
  const assignableUsers = state.users.filter((user) => user.role !== "admin" && !user.isHidden);
  const selectableUsers = isAdmin ? assignableUsers : assignableUsers;
  const selectedRuleUser =
    state.users.find((user) => user.id === Number(adminRuleUserId)) ??
    state.users.find((user) => user.role !== "admin") ??
    state.users[0];

  const activeView = !currentUser
    ? state.currentView === "public"
      ? "public"
      : "login"
    : !isAdmin && state.currentView === "admin"
      ? "dashboard"
      : state.currentView;

  const voterDisplayName = currentUser?.displayName?.trim() || voteName.trim();
  const voteNameLower = useMemo(() => voterDisplayName.toLowerCase(), [voterDisplayName]);

  const loadUsersFromSupabase = async () => {
    const supabase = await getSupabaseClient();
    if (!supabase) {
      return;
    }

    setUsersLoading(true);
    const { data, error } = await supabase
      .from("users")
      .select("id, username, display_name, password_text, email, role, is_hidden")
      .order("id", { ascending: true });

    if (error) {
      setNotice(`Errore Supabase (utenti): ${error.message}`);
      setUsersLoading(false);
      return;
    }

    setState((current) => {
      const mergedUsers = (data || []).map((user) => ({
        id: user.id,
        username: user.username,
        displayName: user.display_name,
        password: user.password_text || "user123",
        email: user.email || "",
        role: user.role,
        isHidden: Boolean(user.is_hidden),
        balance: 0,
        malusRules: [],
      }));
      const nextId =
        mergedUsers.length > 0 ? Math.max(...mergedUsers.map((user) => user.id)) + 1 : 1;
      return deriveState({
        ...current,
        users: mergedUsers,
        nextIds: {
          ...current.nextIds,
          user: nextId,
        },
      });
    });

    setUsersLoading(false);
  };

  const loadMalusTypesFromSupabase = async () => {
    const supabase = await getSupabaseClient();
    if (!supabase) {
      return;
    }

    const { data, error } = await supabase
      .from("malus_types")
      .select("id, code, name, amount, is_active")
      .order("id", { ascending: true });

    if (error) {
      setNotice(`Errore Supabase (malus): ${error.message}`);
      return;
    }

    const normalized = (data || []).map((row) => ({
      id: row.code,
      name: row.name,
      amount: Number(row.amount),
      dbId: row.id,
      isActive: row.is_active,
    }));

    setState((current) =>
      deriveState({
        ...current,
        malusTypes: normalized.filter((item) => item.isActive),
      }),
    );
  };

  const loadRulesFromSupabase = async () => {
    const supabase = await getSupabaseClient();
    if (!supabase) {
      return;
    }

    const { data, error } = await supabase
      .from("user_malus_rules")
      .select("id, user_id, malus_type_id, description");

    if (error) {
      setNotice(`Errore Supabase (regole): ${error.message}`);
      return;
    }

    setState((current) => {
      const malusTypeByDbId = new Map(
        current.malusTypes.map((item) => [item.dbId, item.id]),
      );
      const rulesByUser = new Map();

      (data || []).forEach((rule) => {
        const malusCode = malusTypeByDbId.get(rule.malus_type_id);
        if (!malusCode) {
          return;
        }
        if (!rulesByUser.has(rule.user_id)) {
          rulesByUser.set(rule.user_id, []);
        }
        rulesByUser.get(rule.user_id).push({
          id: rule.id,
          dbId: rule.id,
          malusTypeId: malusCode,
          description: rule.description,
        });
      });

      const users = current.users.map((user) => ({
        ...user,
        malusRules: rulesByUser.get(user.id) || [],
      }));

      return deriveState({
        ...current,
        users,
      });
    });
  };

  const loadTransactionsFromSupabase = async () => {
    const supabase = await getSupabaseClient();
    if (!supabase) {
      return;
    }

    const { data, error } = await supabase
      .from("transactions")
      .select("id, user_id, created_by, malus_type_id, amount, description, cancelled, created_at")
      .order("created_at", { ascending: false });

    if (error) {
      setNotice(`Errore Supabase (transazioni): ${error.message}`);
      return;
    }

    setState((current) => {
      const malusTypeByDbId = new Map(
        current.malusTypes.map((item) => [item.dbId, item.id]),
      );
      const transactions = (data || []).map((row) => ({
        id: row.id,
        userId: row.user_id,
        createdBy: row.created_by,
        type: "malus",
        malusTypeId: malusTypeByDbId.get(row.malus_type_id) || current.malusTypes[0]?.id,
        amount: Number(row.amount),
        description: row.description,
        cancelled: row.cancelled,
        timestamp: row.created_at,
      }));

      return deriveState({
        ...current,
        transactions,
        nextIds: {
          ...current.nextIds,
          transaction: transactions.length > 0 ? Math.max(...transactions.map((t) => t.id)) + 1 : 1,
        },
      });
    });
  };

  const loadPublicProposals = async () => {
    const supabase = await getSupabaseClient();
    if (!supabase) {
      setPublicProposals(state.proposals);
      return;
    }

    setPublicLoading(true);
    setPublicError("");

    try {
      const { data: proposalsData, error: proposalsError } = await supabase
        .from("proposals")
        .select("id, proposer_name, target_name, description, created_at")
        .order("created_at", { ascending: false });

      if (proposalsError) {
        setPublicError(`Errore Supabase (proposte): ${proposalsError.message}`);
        setPublicProposals(state.proposals);
        setPublicLoading(false);
        return;
      }

      const { data: votesData, error: votesError } = await supabase
        .from("proposal_votes")
        .select("proposal_id, voter_name_lower");

      if (votesError) {
        setPublicError(`Errore Supabase (voti): ${votesError.message}`);
        setPublicProposals(state.proposals);
        setPublicLoading(false);
        return;
      }

      const voteMap = new Map();
      votesData.forEach((vote) => {
        if (!voteMap.has(vote.proposal_id)) {
          voteMap.set(vote.proposal_id, []);
        }
        voteMap.get(vote.proposal_id).push(vote.voter_name_lower);
      });

      const merged = proposalsData.map((proposal) => ({
        id: proposal.id,
        proposerName: proposal.proposer_name,
        targetName: proposal.target_name,
        description: proposal.description,
        createdAt: proposal.created_at,
        votes: voteMap.get(proposal.id) || [],
      }));

      setPublicProposals(merged);
      setPublicLoading(false);
    } catch (error) {
      setPublicError("Errore inatteso nel caricamento.");
      setPublicProposals(state.proposals);
      setPublicLoading(false);
    }
  };

  useEffect(() => {
    if (activeView === "public") {
      loadPublicProposals();
    }
  }, [activeView]);

  useEffect(() => {
    (async () => {
      await loadMalusTypesFromSupabase();
      await loadUsersFromSupabase();
      await loadRulesFromSupabase();
      await loadTransactionsFromSupabase();
    })();
  }, []);

  const updateState = (updater, message) => {
    setState((current) => deriveState(typeof updater === "function" ? updater(current) : updater));
    if (message) {
      setNotice(message);
    }
  };

  const login = (event) => {
    event.preventDefault();
    const loginValue = loginForm.username.trim().toLowerCase();
    const user = state.users.find((candidate) => {
      const usernameMatch = candidate.username.toLowerCase() === loginValue;
      const displayMatch = candidate.displayName.toLowerCase() === loginValue;
      return (usernameMatch || displayMatch) && candidate.password === loginForm.password;
    });

    if (!user) {
      setLoginError("Credenziali non valide.");
      return;
    }

    setLoginError("");
    updateState(
      (current) => ({
        ...current,
        currentUserId: user.id,
        currentView: "dashboard",
      }),
      `Accesso effettuato come ${user.displayName}.`,
    );
  };

  const logout = () => {
    updateState(
      (current) => ({
        ...current,
        currentUserId: null,
        currentView: "dashboard",
      }),
      "Sessione chiusa.",
    );
  };

  const switchView = (view) => {
    updateState((current) => ({
      ...current,
      currentView: view,
    }));
  };

  const resetData = () => {
    const fresh = deriveState(createInitialState());
    setState(fresh);
    setNotice("Dati ripristinati ai valori iniziali.");
  };

  const submitQuickMalus = (event) => {
    event.preventDefault();
    if (!currentUser) {
      return;
    }

    const malusType = state.malusTypes.find((item) => item.id === transactionForm.malusTypeId);
    if (!malusType || !transactionForm.userId) {
      return;
    }

    const targetUser = state.users.find((user) => user.id === Number(transactionForm.userId));
    const selectedRule = targetUser?.malusRules?.find((rule) => String(rule.id) === transactionForm.ruleId);
    const description = isAdmin
      ? transactionForm.description.trim()
      : selectedRule?.description?.trim() || "";

    if (!description) {
      setNotice("Seleziona un malus gia impostato.");
      return;
    }

    getSupabaseClient().then((supabase) => {
      if (supabase && malusType.dbId) {
        (async () => {
          const { error } = await supabase.from("transactions").insert({
            user_id: Number(transactionForm.userId),
            created_by: currentUser.id,
            malus_type_id: malusType.dbId,
            amount: Number(malusType.amount),
            description,
            cancelled: false,
          });

          if (error) {
            setNotice(`Errore Supabase (transazioni): ${error.message}`);
            return;
          }

          setNotice("Malus registrato.");
          loadTransactionsFromSupabase();
        })();
      } else {
        updateState((current) => ({
          ...current,
          transactions: [
            {
              id: current.nextIds.transaction,
              userId: Number(transactionForm.userId),
              createdBy: currentUser.id,
              type: "malus",
              malusTypeId: malusType.id,
              amount: Number(malusType.amount),
              description,
              cancelled: false,
              timestamp: new Date().toISOString(),
            },
            ...current.transactions,
          ],
          nextIds: {
            ...current.nextIds,
            transaction: current.nextIds.transaction + 1,
          },
        }), "Malus registrato.");
      }
    });

    setTransactionForm(emptyTransactionForm);
  };

  const submitProposal = (event) => {
    event.preventDefault();
    const proposerName = proposalForm.proposerName.trim();
    const targetName = proposalForm.targetName.trim();
    const description = proposalForm.description.trim();

    if (!proposerName || !targetName || !description) {
      setNotice("Compila tutti i campi della proposta.");
      return;
    }

    const submitLocalProposal = () => {
      updateState((current) => ({
        ...current,
        proposals: [
          {
            id: current.nextIds.proposal,
            proposerName,
            targetName,
            description,
            createdAt: new Date().toISOString(),
            votes: [],
          },
          ...current.proposals,
        ],
        nextIds: {
          ...current.nextIds,
          proposal: current.nextIds.proposal + 1,
        },
      }), "Proposta inserita. Ora si puo votare.");
      setProposalForm(emptyProposalForm);
    };

    getSupabaseClient().then((supabase) => {
      if (!supabase) {
        submitLocalProposal();
        return;
      }

      (async () => {
        const { error } = await supabase.from("proposals").insert({
          proposer_name: proposerName,
          target_name: targetName,
          description,
        });

        if (error) {
          setNotice("Errore nel salvataggio della proposta.");
          return;
        }

        setProposalForm(emptyProposalForm);
        setNotice("Proposta inserita. Ora si puo votare.");
        loadPublicProposals();
      })();
    });
  };

  const voteProposal = (proposalId) => {
    if (!voteNameLower) {
      setNotice("Inserisci il tuo nome per votare.");
      return;
    }

    const voteLocalProposal = () => {
      updateState((current) => ({
        ...current,
        proposals: current.proposals.map((proposal) => {
          if (proposal.id !== proposalId) {
            return proposal;
          }

          if (proposal.votes.includes(voteNameLower)) {
            return proposal;
          }

          return {
            ...proposal,
            votes: [...proposal.votes, voteNameLower],
          };
        }),
      }), "Voto registrato.");
    };

    getSupabaseClient().then((supabase) => {
      if (!supabase) {
        voteLocalProposal();
        return;
      }

      (async () => {
        const { error } = await supabase.from("proposal_votes").insert({
          proposal_id: proposalId,
          voter_name: voterDisplayName,
          voter_name_lower: voteNameLower,
        });

        if (error) {
          if (error.code === "23505") {
            setNotice("Hai gia votato questa proposta.");
            return;
          }
          setNotice("Errore nel voto.");
          return;
        }

        setNotice("Voto registrato.");
        loadPublicProposals();
      })();
    });
  };

  const startEditUser = (user) => {
    setEditingUserId(user.id);
    setUserForm({
      displayName: user.displayName,
      username: user.username,
      password: user.password,
      email: user.email || "",
      role: user.role,
      isHidden: user.isHidden || false,
    });
  };

  const submitUser = (event) => {
    event.preventDefault();

    const normalizedUsername = userForm.username.trim().toLowerCase();
    const nextPayload = {
      id: editingUserId,
      displayName: userForm.displayName.trim(),
      username: normalizedUsername,
      password: userForm.password,
      email: userForm.email.trim(),
      role: userForm.role,
      isHidden: userForm.isHidden,
    };

    getSupabaseClient().then((supabase) => {
      if (supabase) {
        (async () => {
          if (editingUserId) {
            const { error } = await supabase
              .from("users")
              .update({
                username: nextPayload.username,
                display_name: nextPayload.displayName,
                password_text: nextPayload.password,
                email: nextPayload.email || null,
                role: nextPayload.role,
                is_hidden: nextPayload.isHidden,
              })
              .eq("id", editingUserId);

            if (error) {
              setNotice(`Errore Supabase (utente): ${error.message}`);
              return;
            }

            setNotice("Utente aggiornato.");
            loadUsersFromSupabase();
          } else {
            const { error } = await supabase.from("users").insert({
              username: nextPayload.username,
              display_name: nextPayload.displayName,
              password_text: nextPayload.password,
              email: nextPayload.email || null,
              role: nextPayload.role,
              is_hidden: nextPayload.isHidden,
            });

            if (error) {
              setNotice(`Errore Supabase (utente): ${error.message}`);
              return;
            }

            setNotice("Utente creato.");
            loadUsersFromSupabase();
          }
        })();
      } else {
        updateState((current) => {
          const duplicated = current.users.find(
            (user) => user.username === normalizedUsername && user.id !== editingUserId,
          );

          if (duplicated) {
            setNotice("Username gia presente.");
            return current;
          }

          if (editingUserId) {
            return {
              ...current,
              users: current.users.map((user) =>
                user.id === editingUserId
                  ? {
                      ...user,
                      displayName: nextPayload.displayName,
                      username: nextPayload.username,
                      password: nextPayload.password,
                      email: nextPayload.email,
                      role: nextPayload.role,
                      isHidden: nextPayload.isHidden,
                    }
                  : user,
              ),
            };
          }

          return {
            ...current,
            users: [
              ...current.users,
              {
                id: current.nextIds.user,
                displayName: nextPayload.displayName,
                username: nextPayload.username,
                password: nextPayload.password,
                email: nextPayload.email,
                role: nextPayload.role,
                isHidden: nextPayload.isHidden,
                balance: 0,
                malusRules: [],
              },
            ],
            nextIds: {
              ...current.nextIds,
              user: current.nextIds.user + 1,
            },
          };
        }, editingUserId ? "Utente aggiornato." : "Utente creato.");
      }
    });

    setEditingUserId(null);
    setUserForm(emptyUserForm);
  };

  const deleteUser = (userId) => {
    if (userId === currentUser?.id) {
      setNotice("Non puoi eliminare l'utente con cui sei loggato.");
      return;
    }

    getSupabaseClient().then((supabase) => {
      if (supabase) {
        (async () => {
          const { error } = await supabase.from("users").delete().eq("id", userId);
          if (error) {
            setNotice(`Errore Supabase (utente): ${error.message}`);
            return;
          }
          setNotice("Utente eliminato.");
          loadUsersFromSupabase();
        })();
      } else {
        updateState((current) => ({
          ...current,
          users: current.users.filter((user) => user.id !== userId),
          transactions: current.transactions.filter(
            (transaction) => transaction.userId !== userId && transaction.createdBy !== userId,
          ),
        }), "Utente eliminato.");
      }
    });
  };

  const submitMalusType = (event) => {
    event.preventDefault();

    getSupabaseClient().then((supabase) => {
      if (supabase) {
        (async () => {
          if (editingMalusId) {
            const malusItem = state.malusTypes.find((item) => item.id === editingMalusId);
            if (!malusItem?.dbId) {
              return;
            }
            const { error } = await supabase
              .from("malus_types")
              .update({
                name: malusForm.name.trim(),
                amount: Number(malusForm.amount),
              })
              .eq("id", malusItem.dbId);

            if (error) {
              setNotice(`Errore Supabase (malus): ${error.message}`);
              return;
            }
            setNotice("Malus aggiornato.");
            loadMalusTypesFromSupabase();
          } else {
            const code = `malus-${state.nextIds.malusType}`;
            const { error } = await supabase.from("malus_types").insert({
              code,
              name: malusForm.name.trim(),
              amount: Number(malusForm.amount),
              is_active: true,
            });
            if (error) {
              setNotice(`Errore Supabase (malus): ${error.message}`);
              return;
            }
            setNotice("Nuovo malus aggiunto.");
            loadMalusTypesFromSupabase();
          }
        })();
      } else {
        updateState((current) => {
          if (editingMalusId) {
            return {
              ...current,
              malusTypes: current.malusTypes.map((item) =>
                item.id === editingMalusId
                  ? { ...item, name: malusForm.name.trim(), amount: Number(malusForm.amount) }
                  : item,
              ),
            };
          }

          return {
            ...current,
            malusTypes: [
              ...current.malusTypes,
              {
                id: `malus-${current.nextIds.malusType}`,
                name: malusForm.name.trim(),
                amount: Number(malusForm.amount),
              },
            ],
            nextIds: {
              ...current.nextIds,
              malusType: current.nextIds.malusType + 1,
            },
          };
        }, editingMalusId ? "Malus aggiornato." : "Nuovo malus aggiunto.");
      }
    });

    setEditingMalusId(null);
    setMalusForm({ name: "", amount: "0.50" });
  };

  const deleteMalusType = (malusTypeId) => {
    getSupabaseClient().then((supabase) => {
      if (supabase) {
        (async () => {
          const malusItem = state.malusTypes.find((item) => item.id === malusTypeId);
          if (!malusItem?.dbId) {
            return;
          }
          const { error } = await supabase
            .from("malus_types")
            .update({ is_active: false })
            .eq("id", malusItem.dbId);

          if (error) {
            setNotice(`Errore Supabase (malus): ${error.message}`);
            return;
          }
          setNotice("Malus eliminato.");
          loadMalusTypesFromSupabase();
          loadRulesFromSupabase();
          loadTransactionsFromSupabase();
        })();
      } else {
        updateState((current) => ({
          ...current,
          malusTypes: current.malusTypes.filter((item) => item.id !== malusTypeId),
          users: current.users.map((user) => ({
            ...user,
            malusRules: user.malusRules.filter((rule) => rule.malusTypeId !== malusTypeId),
          })),
          transactions: current.transactions.filter((transaction) => transaction.malusTypeId !== malusTypeId),
        }), "Malus eliminato.");
      }
    });
  };

  const startEditRule = (rule) => {
    setEditingRuleId(rule.id);
    setRuleForm({
      malusTypeId: rule.malusTypeId,
      description: rule.description,
    });
  };

  const submitRule = (event) => {
    event.preventDefault();
    if (!selectedRuleUser) {
      return;
    }

    const malusItem = state.malusTypes.find((item) => item.id === ruleForm.malusTypeId);
    getSupabaseClient().then((supabase) => {
      if (supabase && malusItem?.dbId) {
        (async () => {
          if (editingRuleId) {
            const { error } = await supabase
              .from("user_malus_rules")
              .update({
                malus_type_id: malusItem.dbId,
                description: ruleForm.description.trim(),
              })
              .eq("id", editingRuleId);

            if (error) {
              setNotice(`Errore Supabase (regole): ${error.message}`);
              return;
            }
            setNotice("Regola malus aggiornata.");
            loadRulesFromSupabase();
          } else {
            const { error } = await supabase.from("user_malus_rules").insert({
              user_id: selectedRuleUser.id,
              malus_type_id: malusItem.dbId,
              description: ruleForm.description.trim(),
            });

            if (error) {
              setNotice(`Errore Supabase (regole): ${error.message}`);
              return;
            }
            setNotice("Regola malus aggiunta.");
            loadRulesFromSupabase();
          }
        })();
      } else {
        updateState((current) => ({
          ...current,
          users: current.users.map((user) => {
            if (user.id !== selectedRuleUser.id) {
              return user;
            }

            if (editingRuleId) {
              return {
                ...user,
                malusRules: user.malusRules.map((rule) =>
                  rule.id === editingRuleId ? { ...rule, ...ruleForm } : rule,
                ),
              };
            }

            return {
              ...user,
              malusRules: [
                ...user.malusRules,
                {
                  id: current.nextIds.rule,
                  malusTypeId: ruleForm.malusTypeId,
                  description: ruleForm.description.trim(),
                },
              ],
            };
          }),
          nextIds: editingRuleId
            ? current.nextIds
            : {
                ...current.nextIds,
                rule: current.nextIds.rule + 1,
              },
        }), editingRuleId ? "Regola malus aggiornata." : "Regola malus aggiunta.");
      }
    });

    setEditingRuleId(null);
    setRuleForm({ malusTypeId: "", description: "" });
  };

  const deleteRule = (ruleId) => {
    if (!selectedRuleUser) {
      return;
    }

    getSupabaseClient().then((supabase) => {
      if (supabase) {
        (async () => {
          const { error } = await supabase.from("user_malus_rules").delete().eq("id", ruleId);
          if (error) {
            setNotice(`Errore Supabase (regole): ${error.message}`);
            return;
          }
          setNotice("Regola malus eliminata.");
          loadRulesFromSupabase();
        })();
      } else {
        updateState((current) => ({
          ...current,
          users: current.users.map((user) =>
            user.id === selectedRuleUser.id
              ? {
                  ...user,
                  malusRules: user.malusRules.filter((rule) => rule.id !== ruleId),
                }
              : user,
          ),
        }), "Regola malus eliminata.");
      }
    });
  };

  const startEditTransaction = (transaction) => {
    setEditingTransactionId(transaction.id);
    setTransactionForm({
      userId: String(transaction.userId),
      malusTypeId: transaction.malusTypeId,
      description: transaction.description,
      ruleId: "",
    });
  };

  const submitAdminTransaction = (event) => {
    event.preventDefault();
    const malusType = state.malusTypes.find((item) => item.id === transactionForm.malusTypeId);
    if (!malusType || !transactionForm.userId || !currentUser) {
      return;
    }

    getSupabaseClient().then((supabase) => {
      if (supabase && malusType.dbId) {
        (async () => {
          if (editingTransactionId) {
            const { error } = await supabase
              .from("transactions")
              .update({
                user_id: Number(transactionForm.userId),
                malus_type_id: malusType.dbId,
                amount: Number(malusType.amount),
                description: transactionForm.description.trim(),
              })
              .eq("id", editingTransactionId);

            if (error) {
              setNotice(`Errore Supabase (transazioni): ${error.message}`);
              return;
            }

            setNotice("Transazione aggiornata.");
            loadTransactionsFromSupabase();
          } else {
            const { error } = await supabase.from("transactions").insert({
              user_id: Number(transactionForm.userId),
              created_by: currentUser.id,
              malus_type_id: malusType.dbId,
              amount: Number(malusType.amount),
              description: transactionForm.description.trim(),
              cancelled: false,
            });

            if (error) {
              setNotice(`Errore Supabase (transazioni): ${error.message}`);
              return;
            }

            setNotice("Transazione aggiunta.");
            loadTransactionsFromSupabase();
          }
        })();
      } else {
        updateState((current) => {
          if (editingTransactionId) {
            return {
              ...current,
              transactions: current.transactions.map((transaction) =>
                transaction.id === editingTransactionId
                  ? {
                      ...transaction,
                      userId: Number(transactionForm.userId),
                      malusTypeId: malusType.id,
                      amount: Number(malusType.amount),
                      description: transactionForm.description.trim(),
                    }
                  : transaction,
              ),
            };
          }

          return {
            ...current,
            transactions: [
              {
                id: current.nextIds.transaction,
                userId: Number(transactionForm.userId),
                createdBy: currentUser.id,
                type: "malus",
                malusTypeId: malusType.id,
                amount: Number(malusType.amount),
                description: transactionForm.description.trim(),
                cancelled: false,
                timestamp: new Date().toISOString(),
              },
              ...current.transactions,
            ],
            nextIds: {
              ...current.nextIds,
              transaction: current.nextIds.transaction + 1,
            },
          };
        }, editingTransactionId ? "Transazione aggiornata." : "Transazione aggiunta.");
      }
    });

    setEditingTransactionId(null);
    setTransactionForm(emptyTransactionForm);
  };

  const toggleCancelled = (transactionId) => {
    getSupabaseClient().then((supabase) => {
      if (supabase) {
        const currentTx = state.transactions.find((transaction) => transaction.id === transactionId);
        if (!currentTx) {
          return;
        }
        (async () => {
          const { error } = await supabase
            .from("transactions")
            .update({ cancelled: !currentTx.cancelled })
            .eq("id", transactionId);
          if (error) {
            setNotice(`Errore Supabase (transazioni): ${error.message}`);
            return;
          }
          setNotice("Stato transazione aggiornato.");
          loadTransactionsFromSupabase();
        })();
      } else {
        updateState((current) => ({
          ...current,
          transactions: current.transactions.map((transaction) =>
            transaction.id === transactionId
              ? { ...transaction, cancelled: !transaction.cancelled }
              : transaction,
          ),
        }), "Stato transazione aggiornato.");
      }
    });
  };

  const deleteTransaction = (transactionId) => {
    getSupabaseClient().then((supabase) => {
      if (supabase) {
        (async () => {
          const { error } = await supabase.from("transactions").delete().eq("id", transactionId);
          if (error) {
            setNotice(`Errore Supabase (transazioni): ${error.message}`);
            return;
          }
          setNotice("Transazione eliminata.");
          loadTransactionsFromSupabase();
        })();
      } else {
        updateState((current) => ({
          ...current,
          transactions: current.transactions.filter((transaction) => transaction.id !== transactionId),
        }), "Transazione eliminata.");
      }
    });
  };

  if (activeView === "public" && !currentUser) {
    return (
      <div className="app-shell">
        {notice ? <div className="notification success">{notice}</div> : null}
        <div className="container">
          <header className="app-header">
            <div>
              <p className="eyebrow">Bacheca pubblica</p>
              <h1>Proposte malus</h1>
            </div>
            <button className="btn btn-secondary" type="button" onClick={() => switchView("login")}>
              Torna al login
            </button>
          </header>

          <section className="grid two-cols">
            <article className="card">
              <h2>Proponi un malus</h2>
              <p className="muted">Nessun accesso richiesto. Inserisci solo il tuo nome.</p>
              <form className="stack" onSubmit={submitProposal}>
                <label>
                  Il tuo nome
                  <input
                    value={proposalForm.proposerName}
                    onChange={(event) =>
                      setProposalForm((current) => ({ ...current, proposerName: event.target.value }))
                    }
                    required
                  />
                </label>
                <label>
                  Nome della persona
                  <input
                    value={proposalForm.targetName}
                    onChange={(event) =>
                      setProposalForm((current) => ({ ...current, targetName: event.target.value }))
                    }
                    required
                  />
                </label>
                <label>
                  Descrizione del malus
                  <textarea
                    rows="3"
                    value={proposalForm.description}
                    onChange={(event) =>
                      setProposalForm((current) => ({ ...current, description: event.target.value }))
                    }
                    required
                  />
                </label>
                <button className="btn btn-primary" type="submit">
                  Invia proposta
                </button>
              </form>
            </article>

            <article className="card">
              <h2>Vota le proposte</h2>
              {currentUser ? (
                <p className="muted">Stai votando come {currentUser.displayName}.</p>
              ) : (
                <label>
                  Il tuo nome (per votare)
                  <input value={voteName} onChange={(event) => setVoteName(event.target.value)} />
                </label>
              )}
              <div className="stack top-gap">
                {publicLoading ? <p className="muted">Caricamento...</p> : null}
                {publicError ? <p className="error-text">{publicError}</p> : null}
                {!publicLoading && !publicError && publicProposals.length ? (
                  [...publicProposals]
                    .sort((a, b) => b.votes.length - a.votes.length)
                    .map((proposal) => (
                      <div className="proposal-card" key={proposal.id}>
                        <div className="proposal-header">
                          <div>
                            <strong>{proposal.targetName}</strong>
                            <p className="muted">
                              Proposto da {proposal.proposerName} · {formatDate(proposal.createdAt)}
                            </p>
                          </div>
                          <div className="vote-count">{proposal.votes.length} voti</div>
                        </div>
                        <p>{proposal.description}</p>
                        <button
                          className="btn btn-accent"
                          type="button"
                          onClick={() => voteProposal(proposal.id)}
                          disabled={!voteNameLower || proposal.votes.includes(voteNameLower)}
                        >
                          {proposal.votes.includes(voteNameLower) ? "Hai gia votato" : "Vota"}
                        </button>
                      </div>
                    ))
                ) : (
                  <p className="muted">Nessuna proposta ancora.</p>
                )}
              </div>
            </article>
          </section>
          <footer className="app-footer">Creato by Anselmo Acquah</footer>
        </div>
      </div>
    );
  }

  if (activeView === "login") {
    return (
      <div className="app-shell login-shell">
        <div className="login-card">
          <p className="eyebrow">React rebuild</p>
          <h1>Fanta W2D</h1>
          <p className="muted">
            Edizione 2026: malus, risate e zero scuse. Una bacheca per le leggende dell'ufficio.
          </p>
          <form onSubmit={login} className="stack">
            <label>
              Username
              <input
                placeholder="Inserisci username"
                value={loginForm.username}
                onChange={(event) => setLoginForm((current) => ({ ...current, username: event.target.value }))}
              />
            </label>
            <label>
              Password
              <input
                type="password"
                placeholder="Inserisci password"
                value={loginForm.password}
                onChange={(event) => setLoginForm((current) => ({ ...current, password: event.target.value }))}
              />
            </label>
            {loginError ? <p className="error-text">{loginError}</p> : null}
            <button className="btn btn-primary" type="submit">
              Accedi
            </button>
          </form>
          <button className="btn btn-accent top-gap" type="button" onClick={() => switchView("public")}>
            Vai alla bacheca pubblica malus
          </button>
          <footer className="app-footer">Creato by Anselmo Acquah</footer>
        </div>
      </div>
    );
  }

  const recentTransactions = state.transactions.slice(0, 6);

  return (
    <div className="app-shell">
      {notice ? <div className="notification success">{notice}</div> : null}
      <div className="container">
        <header className="app-header">
          <div>
            <p className="eyebrow">Fanta W2D</p>
            <h1>Salvadanaio malus</h1>
          </div>
          {currentUser ? (
            <div className="user-strip">
              <div>
                <strong>{currentUser.displayName}</strong>
                <p className="muted">{currentUser.role === "admin" ? "Accesso amministratore" : "Utente standard"}</p>
              </div>
              <button className="btn btn-secondary" type="button" onClick={logout}>
                Logout
              </button>
            </div>
          ) : null}
        </header>

        <nav className="nav-tabs">
          <button className={activeView === "dashboard" ? "active" : ""} onClick={() => switchView("dashboard")}>
            Home
          </button>
          <button className={activeView === "leaderboard" ? "active" : ""} onClick={() => switchView("leaderboard")}>
            Classifica
          </button>
          <button className={activeView === "transactions" ? "active" : ""} onClick={() => switchView("transactions")}>
            Transazioni
          </button>
          <button className={activeView === "profile" ? "active" : ""} onClick={() => switchView("profile")}>
            Profilo
          </button>
          <button className={activeView === "public" ? "active" : ""} onClick={() => switchView("public")}>
            Proposte malus
          </button>
          {isAdmin ? (
            <button className={activeView === "admin" ? "active" : ""} onClick={() => switchView("admin")}>
              Admin
            </button>
          ) : null}
        </nav>

        {activeView === "dashboard" ? (
          <section className="grid two-cols">
            <article className="card balance-card">
              <h2>Saldo attuale</h2>
              <div className={`amount ${currentUser.balance < 0 ? "negative" : ""}`}>
                {formatCurrency(currentUser.balance)}
              </div>
              <p className="muted">
                Il saldo dipende solo dai malus attivi. Le correzioni passano dal pannello admin.
              </p>
            </article>

            <article className="card">
              <h2>Assegna malus</h2>
              {!isAdmin ? (
                <p className="muted">
                  Gli utenti normali possono scegliere solo malus gia impostati. Le nuove regole vengono aggiunte
                  dall&apos;admin dopo le votazioni.
                </p>
              ) : null}
              <form className="stack" onSubmit={submitQuickMalus}>
                <label>
                  Utente
                  <select
                    value={transactionForm.userId}
                    onChange={(event) =>
                      setTransactionForm((current) => ({
                        ...current,
                        userId: event.target.value,
                        ruleId: "",
                      }))
                    }
                    required
                  >
                    <option value="">Seleziona</option>
                    {assignableUsers.map((user) => (
                      <option key={user.id} value={user.id}>
                        {user.displayName}
                      </option>
                    ))}
                  </select>
                </label>
                <label>
                  Tipo malus
                  <select
                    value={transactionForm.malusTypeId}
                    onChange={(event) =>
                      setTransactionForm((current) => ({ ...current, malusTypeId: event.target.value }))
                    }
                    required
                  >
                    <option value="">Seleziona</option>
                    {state.malusTypes.map((malus) => (
                      <option key={malus.id} value={malus.id}>
                        {malus.name} - {formatCurrency(malus.amount)}
                      </option>
                    ))}
                  </select>
                </label>
                {isAdmin ? (
                  <label>
                    Descrizione
                    <textarea
                      rows="3"
                      value={transactionForm.description}
                      onChange={(event) =>
                        setTransactionForm((current) => ({ ...current, description: event.target.value }))
                      }
                      placeholder="Motivo del malus"
                      required
                    />
                  </label>
                ) : (
                  <label>
                    Malus preimpostato
                    <select
                      value={transactionForm.ruleId}
                      onChange={(event) =>
                        setTransactionForm((current) => ({ ...current, ruleId: event.target.value }))
                      }
                      required
                      disabled={!transactionForm.userId}
                    >
                      <option value="">Seleziona un malus</option>
                      {(state.users.find((user) => user.id === Number(transactionForm.userId))?.malusRules || []).map(
                        (rule) => (
                          <option key={rule.id} value={rule.id}>
                            {rule.description}
                          </option>
                        ),
                      )}
                    </select>
                  </label>
                )}
                <button className="btn btn-primary" type="submit">
                  Registra malus
                </button>
              </form>
            </article>

            <article className="card span-2">
              <div className="section-head">
                <h2>Ultime transazioni</h2>
                <button className="btn btn-secondary" type="button" onClick={() => switchView("transactions")}>
                  Vedi tutto
                </button>
              </div>
              <div className="transaction-list">
                {recentTransactions.map((transaction) => (
                  <TransactionItem
                    key={transaction.id}
                    transaction={transaction}
                    users={state.users}
                    malusTypes={state.malusTypes}
                  />
                ))}
              </div>
            </article>
          </section>
        ) : null}

        {activeView === "leaderboard" ? (
          <section className="card">
            <h2>Classifica</h2>
            <p className="muted">Utenti con saldo peggiore in cima.</p>
            <div className="leaderboard-list">
              {[...visibleUsers]
                .sort((a, b) => a.balance - b.balance)
                .map((user, index) => (
                  <div className={`leaderboard-row ${user.id === currentUser.id ? "current-user" : ""}`} key={user.id}>
                    <div className="rank">#{index + 1}</div>
                    <div>
                      <strong>{user.displayName}</strong>
                      <p className="muted">{user.username}</p>
                    </div>
                    <div className={`balance-pill ${user.balance < 0 ? "negative" : ""}`}>
                      {formatCurrency(user.balance)}
                    </div>
                    <div className="badge badge-available">{user.role === "admin" ? "Admin" : "User"}</div>
                  </div>
                ))}
            </div>
          </section>
        ) : null}

        {activeView === "transactions" ? (
          <section className="card">
            <h2>Tutte le transazioni</h2>
            <div className="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Utente</th>
                    <th>Malus</th>
                    <th>Importo</th>
                    <th>Descrizione</th>
                    <th>Creato da</th>
                    <th>Data</th>
                    <th>Stato</th>
                  </tr>
                </thead>
                <tbody>
                  {state.transactions.map((transaction) => (
                    <tr key={transaction.id} className={transaction.cancelled ? "cancelled" : ""}>
                      <td>{transaction.id}</td>
                      <td>{labelForUser(state.users, transaction.userId)}</td>
                      <td>{labelForMalus(state.malusTypes, transaction.malusTypeId)}</td>
                      <td className="negative">-{formatCurrency(transaction.amount)}</td>
                      <td>{transaction.description}</td>
                      <td>{labelForUser(state.users, transaction.createdBy)}</td>
                      <td>{formatDate(transaction.timestamp)}</td>
                      <td>{transaction.cancelled ? "Annullato" : "Attivo"}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </section>
        ) : null}

        {activeView === "profile" ? (
          <section className="grid two-cols">
            <article className="card">
              <h2>Profilo</h2>
              <div className="stack compact">
                <div className="info-row">
                  <span>Nome</span>
                  <strong>{currentUser.displayName}</strong>
                </div>
                <div className="info-row">
                  <span>Username</span>
                  <strong>{currentUser.username}</strong>
                </div>
                <div className="info-row">
                  <span>Email</span>
                  <strong>{currentUser.email || "-"}</strong>
                </div>
                <div className="info-row">
                  <span>Ruolo</span>
                  <strong>{currentUser.role}</strong>
                </div>
              </div>
            </article>
            <article className="card">
              <h2>Regole malus personali</h2>
              <div className="stack compact">
                {currentUser.malusRules.length ? (
                  currentUser.malusRules.map((rule) => (
                    <div className="rule-card" key={rule.id}>
                      <strong>{labelForMalus(state.malusTypes, rule.malusTypeId)}</strong>
                      <p>{rule.description}</p>
                    </div>
                  ))
                ) : (
                  <p className="muted">Nessuna regola personalizzata.</p>
                )}
              </div>
            </article>
          </section>
        ) : null}

        {activeView === "public" ? (
          <section className="grid two-cols">
            <article className="card">
              <h2>Proponi un malus</h2>
              <p className="muted">Nessun accesso richiesto. Inserisci solo il tuo nome.</p>
              <form className="stack" onSubmit={submitProposal}>
                <label>
                  Il tuo nome
                  <input
                    value={proposalForm.proposerName}
                    onChange={(event) =>
                      setProposalForm((current) => ({ ...current, proposerName: event.target.value }))
                    }
                    required
                  />
                </label>
                <label>
                  Nome della persona
                  <input
                    value={proposalForm.targetName}
                    onChange={(event) =>
                      setProposalForm((current) => ({ ...current, targetName: event.target.value }))
                    }
                    required
                  />
                </label>
                <label>
                  Descrizione del malus
                  <textarea
                    rows="3"
                    value={proposalForm.description}
                    onChange={(event) =>
                      setProposalForm((current) => ({ ...current, description: event.target.value }))
                    }
                    required
                  />
                </label>
                <button className="btn btn-primary" type="submit">
                  Invia proposta
                </button>
              </form>
            </article>

            <article className="card">
              <h2>Vota le proposte</h2>
              {currentUser ? (
                <p className="muted">Stai votando come {currentUser.displayName}.</p>
              ) : (
                <label>
                  Il tuo nome (per votare)
                  <input value={voteName} onChange={(event) => setVoteName(event.target.value)} />
                </label>
              )}
              <div className="stack top-gap">
                {publicLoading ? <p className="muted">Caricamento...</p> : null}
                {publicError ? <p className="error-text">{publicError}</p> : null}
                {!publicLoading && !publicError && publicProposals.length ? (
                  [...publicProposals]
                    .sort((a, b) => b.votes.length - a.votes.length)
                    .map((proposal) => (
                      <div className="proposal-card" key={proposal.id}>
                        <div className="proposal-header">
                          <div>
                            <strong>{proposal.targetName}</strong>
                            <p className="muted">
                              Proposto da {proposal.proposerName} · {formatDate(proposal.createdAt)}
                            </p>
                          </div>
                          <div className="vote-count">{proposal.votes.length} voti</div>
                        </div>
                        <p>{proposal.description}</p>
                        <button
                          className="btn btn-accent"
                          type="button"
                          onClick={() => voteProposal(proposal.id)}
                          disabled={!voteNameLower || proposal.votes.includes(voteNameLower)}
                        >
                          {proposal.votes.includes(voteNameLower) ? "Hai gia votato" : "Vota"}
                        </button>
                      </div>
                    ))
                ) : (
                  !publicLoading && !publicError ? <p className="muted">Nessuna proposta ancora.</p> : null
                )}
              </div>
            </article>
          </section>
        ) : null}

        {activeView === "admin" && isAdmin ? (
          <section className="admin-grid">
            <article className="card">
              <div className="section-head">
                <h2>Utenti</h2>
                <span className="muted">L'admin gestisce correzioni e controlli manuali.</span>
              </div>
              <div className="filter-row">
                <span className="muted">Mostra:</span>
                <button
                  className={`btn btn-secondary ${adminUserFilter === "active" ? "active-filter" : ""}`}
                  type="button"
                  onClick={() => setAdminUserFilter("active")}
                >
                  Attivi
                </button>
                <button
                  className={`btn btn-secondary ${adminUserFilter === "hidden" ? "active-filter" : ""}`}
                  type="button"
                  onClick={() => setAdminUserFilter("hidden")}
                >
                  Nascosti
                </button>
                <button
                  className={`btn btn-secondary ${adminUserFilter === "all" ? "active-filter" : ""}`}
                  type="button"
                  onClick={() => setAdminUserFilter("all")}
                >
                  Tutti
                </button>
              </div>
              {usersLoading ? <p className="muted">Sincronizzazione utenti...</p> : null}
              <form className="stack" onSubmit={submitUser}>
                <label>
                  Nome visualizzato
                  <input
                    value={userForm.displayName}
                    onChange={(event) =>
                      setUserForm((current) => ({ ...current, displayName: event.target.value }))
                    }
                    required
                  />
                </label>
                <label>
                  Username
                  <input
                    value={userForm.username}
                    onChange={(event) =>
                      setUserForm((current) => ({ ...current, username: event.target.value }))
                    }
                    required
                  />
                </label>
                <label>
                  Password
                  <input
                    value={userForm.password}
                    onChange={(event) =>
                      setUserForm((current) => ({ ...current, password: event.target.value }))
                    }
                    required
                  />
                </label>
                <label>
                  Email
                  <input
                    value={userForm.email}
                    onChange={(event) => setUserForm((current) => ({ ...current, email: event.target.value }))}
                  />
                </label>
                <label>
                  Ruolo
                  <select
                    value={userForm.role}
                    onChange={(event) => setUserForm((current) => ({ ...current, role: event.target.value }))}
                  >
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                  </select>
                </label>
                <label className="checkbox-row">
                  <input
                    type="checkbox"
                    checked={userForm.isHidden}
                    onChange={(event) =>
                      setUserForm((current) => ({ ...current, isHidden: event.target.checked }))
                    }
                  />
                  Nascondi da classifica e gioco
                </label>
                <div className="actions-row">
                  <button className="btn btn-primary" type="submit">
                    {editingUserId ? "Salva utente" : "Aggiungi utente"}
                  </button>
                  {editingUserId ? (
                    <button
                      className="btn btn-secondary"
                      type="button"
                      onClick={() => {
                        setEditingUserId(null);
                        setUserForm(emptyUserForm);
                      }}
                    >
                      Annulla
                    </button>
                  ) : null}
                </div>
              </form>
              <div className="stack compact">
                {(adminUserFilter === "all"
                  ? state.users
                  : adminUserFilter === "hidden"
                    ? state.users.filter((user) => user.isHidden)
                    : state.users.filter((user) => !user.isHidden)
                ).map((user) => (
                  <div className="admin-item" key={user.id}>
                    <div>
                      <strong>{user.displayName}</strong>
                      <p className="muted">
                        {user.username} · {user.role} · {formatCurrency(user.balance)}
                        {user.isHidden ? " · nascosto" : ""}
                      </p>
                    </div>
                    <div className="actions-row">
                      <button className="btn btn-accent" type="button" onClick={() => startEditUser(user)}>
                        Modifica
                      </button>
                      <button className="btn btn-danger" type="button" onClick={() => deleteUser(user.id)}>
                        Elimina
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            </article>

            <article className="card">
              <h2>Tipi di malus</h2>
              <form className="stack" onSubmit={submitMalusType}>
                <label>
                  Nome
                  <input
                    value={malusForm.name}
                    onChange={(event) => setMalusForm((current) => ({ ...current, name: event.target.value }))}
                    required
                  />
                </label>
                <label>
                  Importo
                  <input
                    type="number"
                    min="0.1"
                    step="0.1"
                    value={malusForm.amount}
                    onChange={(event) => setMalusForm((current) => ({ ...current, amount: event.target.value }))}
                    required
                  />
                </label>
                <div className="actions-row">
                  <button className="btn btn-primary" type="submit">
                    {editingMalusId ? "Salva malus" : "Aggiungi malus"}
                  </button>
                  {editingMalusId ? (
                    <button
                      className="btn btn-secondary"
                      type="button"
                      onClick={() => {
                        setEditingMalusId(null);
                        setMalusForm({ name: "", amount: "0.50" });
                      }}
                    >
                      Annulla
                    </button>
                  ) : null}
                </div>
              </form>
              <div className="stack compact">
                {state.malusTypes.map((malus) => (
                  <div className="admin-item" key={malus.id}>
                    <div>
                      <strong>{malus.name}</strong>
                      <p className="muted">{formatCurrency(malus.amount)}</p>
                    </div>
                    <div className="actions-row">
                      <button
                        className="btn btn-accent"
                        type="button"
                        onClick={() => {
                          setEditingMalusId(malus.id);
                          setMalusForm({ name: malus.name, amount: String(malus.amount) });
                        }}
                      >
                        Modifica
                      </button>
                      <button className="btn btn-danger" type="button" onClick={() => deleteMalusType(malus.id)}>
                        Elimina
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            </article>

            <article className="card">
              <h2>Regole malus per utente</h2>
              <label>
                Utente
                <select
                  value={selectedRuleUser?.id ?? ""}
                  onChange={(event) => setAdminRuleUserId(Number(event.target.value))}
                >
                  {state.users.map((user) => (
                    <option key={user.id} value={user.id}>
                      {user.displayName}
                    </option>
                  ))}
                </select>
              </label>
              <form className="stack top-gap" onSubmit={submitRule}>
                <label>
                  Tipo malus
                  <select
                    value={ruleForm.malusTypeId}
                    onChange={(event) => setRuleForm((current) => ({ ...current, malusTypeId: event.target.value }))}
                    required
                  >
                    <option value="">Seleziona</option>
                    {state.malusTypes.map((malus) => (
                      <option key={malus.id} value={malus.id}>
                        {malus.name}
                      </option>
                    ))}
                  </select>
                </label>
                <label>
                  Descrizione
                  <textarea
                    rows="3"
                    value={ruleForm.description}
                    onChange={(event) => setRuleForm((current) => ({ ...current, description: event.target.value }))}
                    required
                  />
                </label>
                <div className="actions-row">
                  <button className="btn btn-primary" type="submit">
                    {editingRuleId ? "Salva regola" : "Aggiungi regola"}
                  </button>
                  {editingRuleId ? (
                    <button
                      className="btn btn-secondary"
                      type="button"
                      onClick={() => {
                        setEditingRuleId(null);
                        setRuleForm({ malusTypeId: "", description: "" });
                      }}
                    >
                      Annulla
                    </button>
                  ) : null}
                </div>
              </form>
              <div className="stack compact">
                {(selectedRuleUser?.malusRules || []).map((rule) => (
                  <div className="admin-item" key={rule.id}>
                    <div>
                      <strong>{labelForMalus(state.malusTypes, rule.malusTypeId)}</strong>
                      <p className="muted">{rule.description}</p>
                    </div>
                    <div className="actions-row">
                      <button className="btn btn-accent" type="button" onClick={() => startEditRule(rule)}>
                        Modifica
                      </button>
                      <button className="btn btn-danger" type="button" onClick={() => deleteRule(rule.id)}>
                        Elimina
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            </article>

            <article className="card">
              <div className="section-head">
                <h2>Transazioni</h2>
                <button className="btn btn-danger" type="button" onClick={resetData}>
                  Reset dati demo
                </button>
              </div>
              <form className="stack" onSubmit={submitAdminTransaction}>
                <label>
                  Utente
                  <select
                    value={transactionForm.userId}
                    onChange={(event) =>
                      setTransactionForm((current) => ({ ...current, userId: event.target.value }))
                    }
                    required
                  >
                    <option value="">Seleziona</option>
                    {state.users.map((user) => (
                      <option key={user.id} value={user.id}>
                        {user.displayName}
                      </option>
                    ))}
                  </select>
                </label>
                <label>
                  Tipo malus
                  <select
                    value={transactionForm.malusTypeId}
                    onChange={(event) =>
                      setTransactionForm((current) => ({ ...current, malusTypeId: event.target.value }))
                    }
                    required
                  >
                    <option value="">Seleziona</option>
                    {state.malusTypes.map((malus) => (
                      <option key={malus.id} value={malus.id}>
                        {malus.name} - {formatCurrency(malus.amount)}
                      </option>
                    ))}
                  </select>
                </label>
                <label>
                  Descrizione
                  <textarea
                    rows="3"
                    value={transactionForm.description}
                    onChange={(event) =>
                      setTransactionForm((current) => ({ ...current, description: event.target.value }))
                    }
                    required
                  />
                </label>
                <div className="actions-row">
                  <button className="btn btn-primary" type="submit">
                    {editingTransactionId ? "Salva transazione" : "Aggiungi transazione"}
                  </button>
                  {editingTransactionId ? (
                    <button
                      className="btn btn-secondary"
                      type="button"
                      onClick={() => {
                        setEditingTransactionId(null);
                        setTransactionForm(emptyTransactionForm);
                      }}
                    >
                      Annulla
                    </button>
                  ) : null}
                </div>
              </form>
              <div className="stack compact">
                {state.transactions.map((transaction) => (
                  <div className="admin-item" key={transaction.id}>
                    <div>
                      <strong>
                        {labelForUser(state.users, transaction.userId)} ·{" "}
                        {labelForMalus(state.malusTypes, transaction.malusTypeId)}
                      </strong>
                      <p className="muted">
                        -{formatCurrency(transaction.amount)} · {transaction.description} ·{" "}
                        {formatDate(transaction.timestamp)}
                      </p>
                    </div>
                    <div className="actions-row">
                      <button className="btn btn-accent" type="button" onClick={() => startEditTransaction(transaction)}>
                        Modifica
                      </button>
                      <button className="btn btn-secondary" type="button" onClick={() => toggleCancelled(transaction.id)}>
                        {transaction.cancelled ? "Riattiva" : "Annulla"}
                      </button>
                      <button className="btn btn-danger" type="button" onClick={() => deleteTransaction(transaction.id)}>
                        Elimina
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            </article>
          </section>
        ) : null}
        <footer className="app-footer">Creato by Anselmo Acquah</footer>
      </div>
    </div>
  );
}

function TransactionItem({ transaction, users, malusTypes }) {
  return (
    <div className={`transaction-item ${transaction.cancelled ? "cancelled" : ""}`}>
      <div>
        <strong>{labelForUser(users, transaction.userId)}</strong>
        <p className="muted">
          {labelForMalus(malusTypes, transaction.malusTypeId)} · {transaction.description}
        </p>
      </div>
      <div className="transaction-meta">
        <span className="negative">-{formatCurrency(transaction.amount)}</span>
        <small>{formatDate(transaction.timestamp)}</small>
      </div>
    </div>
  );
}

const labelForUser = (users, userId) => users.find((user) => user.id === userId)?.displayName || "Utente";
const labelForMalus = (malusTypes, malusTypeId) =>
  malusTypes.find((malus) => malus.id === malusTypeId)?.name || "Malus";
const formatDate = (value) =>
  new Intl.DateTimeFormat("it-IT", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  }).format(new Date(value));

export default App;
